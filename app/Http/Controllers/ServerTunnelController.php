<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServerTunnelController extends Controller
{
    /**
     * Muestra la vista de control del Servidor Remoto (Túnel Ngrok).
     */
    public function index()
    {
        $status = $this->checkTunnelStatus();

        return view('server.index', [
            'isRunning'  => $status['is_running'],
            'publicUrl'  => $status['url'],
            'proto'      => $status['proto'] ?? 'https',
            'port'       => env('NGROK_PORT', 8000),
            'hasToken'   => !empty(env('NGROK_AUTHTOKEN')),
            'localIp'    => $this->getLocalIpAddress(),
        ]);
    }

    /**
     * Devuelve el estado actual en JSON (para actualización en tiempo real).
     */
    public function status()
    {
        $status = $this->checkTunnelStatus();

        return response()->json([
            'is_running' => $status['is_running'],
            'public_url' => $status['url'],
            'proto'      => $status['proto'] ?? 'https',
            'port'       => env('NGROK_PORT', 8000),
            'has_token'  => !empty(env('NGROK_AUTHTOKEN')),
            'local_ip'   => $this->getLocalIpAddress(),
        ]);
    }

    /**
     * Guarda el token de servidor en el archivo .env y configura ngrok.
     */
    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|min:10',
        ]);

        $token = trim($request->input('token'));
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            $env = file_get_contents($envPath);
            if (preg_match('/^NGROK_AUTHTOKEN=.*/m', $env)) {
                $env = preg_replace('/^NGROK_AUTHTOKEN=.*/m', 'NGROK_AUTHTOKEN=' . $token, $env);
            } else {
                $env .= "\nNGROK_AUTHTOKEN=" . $token . "\n";
            }
            file_put_contents($envPath, $env);
        }

        $ngrokCmd = $this->ensureNgrokExecutable();
        if ($ngrokCmd) {
            @exec($ngrokCmd . ' config add-authtoken ' . escapeshellarg($token) . ' 2>&1');
        }

        return response()->json([
            'success' => true,
            'message' => 'Clave de servidor guardada correctamente.',
        ]);
    }

    /**
     * Alterna (toggle) el estado del servidor.
     */
    public function toggle(Request $request)
    {
        $status = $this->checkTunnelStatus();

        if ($status['is_running']) {
            return $this->stop($request);
        } else {
            return $this->start($request);
        }
    }

    /**
     * Inicia el túnel Ngrok en segundo plano.
     */
    public function start(Request $request)
    {
        $status = $this->checkTunnelStatus();
        if ($status['is_running'] && !empty($status['url'])) {
            return response()->json([
                'success'    => true,
                'message'    => 'El servidor ya se encuentra activo.',
                'public_url' => $status['url'],
            ]);
        }

        // 1. Asegurar authtoken configurado
        $token = env('NGROK_AUTHTOKEN');
        if (empty($token)) {
            return response()->json([
                'success'     => false,
                'needs_token' => true,
                'message'     => 'Primero debes ingresar tu clave de servidor.',
            ], 422);
        }

        $ngrokCmd = $this->ensureNgrokExecutable();
        if (!$ngrokCmd) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el ejecutable ngrok.exe. Por favor verifica la instalación.',
            ], 500);
        }

        @exec($ngrokCmd . ' config add-authtoken ' . escapeshellarg($token) . ' 2>&1');

        // 2. Limpiar cualquier proceso previo colgado
        @exec('taskkill /F /IM ngrok.exe 2>&1');
        usleep(300000); // 300ms

        // 3. Iniciar proceso en segundo plano en Windows
        $port = (int) env('NGROK_PORT', 8000);
        $logPath = storage_path('logs/ngrok.log');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = 'start /B "" ' . $ngrokCmd . ' http ' . $port . ' --log="' . $logPath . '" --log-format=json';
            pclose(popen($cmd, "r"));
        } else {
            $cmd = $ngrokCmd . ' http ' . $port . ' --log="' . $logPath . '" --log-format=json > /dev/null 2>&1 &';
            exec($cmd);
        }

        // 4. Polling breve para esperar que Ngrok levante el API local en el puerto 4040
        $publicUrl = null;
        for ($i = 0; $i < 10; $i++) {
            usleep(500000); // 500ms
            $check = $this->checkTunnelStatus();
            if ($check['is_running'] && !empty($check['url'])) {
                $publicUrl = $check['url'];
                break;
            }
        }

        if ($publicUrl) {
            return response()->json([
                'success'    => true,
                'message'    => 'Servidor remoto iniciado con éxito.',
                'public_url' => $publicUrl,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo obtener el enlace público de Ngrok. Revisa la conexión a internet.',
        ], 500);
    }

    /**
     * Detiene el túnel Ngrok matando el proceso.
     */
    public function stop(Request $request)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            @exec('taskkill /F /IM ngrok.exe 2>&1');
        } else {
            @exec('pkill -f ngrok 2>&1');
        }

        usleep(400000); // 400ms

        return response()->json([
            'success' => true,
            'message' => 'Servidor remoto detenido correctamente.',
        ]);
    }

    /**
     * Obtiene el comando o ruta al ejecutable de ngrok.
     */
    private function ensureNgrokExecutable(): ?string
    {
        // 1. Verificar si existe ngrok.exe en la raíz del proyecto
        $localExe = base_path('ngrok.exe');
        if (file_exists($localExe)) {
            return '"' . $localExe . '"';
        }

        // 2. Verificar si está en el PATH del sistema
        $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $check = $isWin ? 'where ngrok 2>nul' : 'which ngrok 2>/dev/null';
        $output = @shell_exec($check);
        if (!empty(trim((string)$output))) {
            return 'ngrok';
        }

        // 3. Intento de descarga automática si no existe en Windows
        if ($isWin) {
            try {
                $zipUrl = 'https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-windows-amd64.zip';
                $zipFile = storage_path('app/ngrok.zip');

                if (!is_dir(storage_path('app'))) {
                    @mkdir(storage_path('app'), 0777, true);
                }

                $content = @file_get_contents($zipUrl);
                if ($content) {
                    file_put_contents($zipFile, $content);
                    $zip = new \ZipArchive();
                    if ($zip->open($zipFile) === true) {
                        $zip->extractTo(base_path(), ['ngrok.exe']);
                        $zip->close();
                    }
                    @unlink($zipFile);
                    if (file_exists($localExe)) {
                        return '"' . $localExe . '"';
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error descargando ngrok: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Consulta el estado del túnel conectando con el API interno de Ngrok (puerto 4040).
     */
    private function checkTunnelStatus(): array
    {
        try {
            $response = Http::timeout(1.2)->get('http://127.0.0.1:4040/api/tunnels');

            if ($response->successful()) {
                $data = $response->json();
                $tunnels = $data['tunnels'] ?? [];

                if (!empty($tunnels)) {
                    // Priorizar túnel https
                    $httpsTunnel = collect($tunnels)->firstWhere('proto', 'https') ?? $tunnels[0];
                    return [
                        'is_running' => true,
                        'url'        => $httpsTunnel['public_url'] ?? null,
                        'proto'      => $httpsTunnel['proto'] ?? 'https',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Ngrok no está respondiendo en el puerto 4040 (apagado)
        }

        return [
            'is_running' => false,
            'url'        => null,
            'proto'      => 'https',
        ];
    }

    /**
     * Obtiene la IP local en la red WiFi/Ethernet para acceso en la misma red.
     */
    private function getLocalIpAddress(): string
    {
        $ip = gethostbyname(gethostname());
        return ($ip && $ip !== '127.0.0.1') ? $ip : '192.168.1.X';
    }
}
