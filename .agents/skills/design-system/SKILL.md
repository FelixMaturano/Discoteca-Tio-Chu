---
name: design-system
description: Reglas y estándares de diseño minimalista iOS, colores oficiales de Tío Chu y principios UX/UI.
---

# Reglas de Diseño y UX/UI — Tío Chu

Consulta siempre el archivo [design.md](file:///c:/Users/Usuario/Desktop/Tio%20Chu/design.md) en la raíz del proyecto para detalles completos.

## Puntos Críticos:
1. **Fondo:** Negro puro (`#000000`). Cero tonos plomos o grises lavados.
2. **Acento:** Dorado oficial `#F5B81C` (ámbar cerveza de la marca). No usar naranjas saturados.
3. **Copywriting:** No inventar frases artificiales ("Acceso Ejecutivo", "Sistema de Alta Gama", etc.). Solo "Tío Chu".
4. **Formularios:** Estilo iOS minimalista, bordes finos `border-zinc-800`, sin cards pesadas de IA.
5. **Feedback / Alertas:** Toasts efímeros abajo al centro (`fixed bottom-6 left-1/2 -translate-x-1/2`), fondo negro sutil, desvanecimiento automático en 1.5s - 2s. No banners verdes/rojos estáticos invasivos.
6. **Animaciones:** Utilizar GSAP para animaciones cinematográficas de alto rendimiento (60fps), aceleradas por hardware sin tirones.
