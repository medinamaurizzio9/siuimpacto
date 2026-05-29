# IMPACTO URBANIZACIONES

Sistema Integral de Terrenos  
Version piloto - MVP funcional

## Credenciales de acceso

| Rol | Email | Contrasena |
| --- | --- | --- |
| Administrador | admin@impacto.test | password |
| Gerente | gerente@impacto.test | password |
| Vendedor | vendedor@impacto.test | password |
| Cliente | cliente@impacto.test | password |

## Flujo recomendado para demo

1. Ingresar como administrador o gerente.
2. Revisar el dashboard ejecutivo: disponibilidad, ingresos, cuotas vencidas y reservas proximas.
3. Abrir el mapa de disponibilidad y mostrar los colores por estado.
4. Crear una reserva sobre un lote disponible.
5. Convertir una reserva en venta desde el formulario de ventas seleccionando el mismo cliente y lote reservado.
6. Registrar una venta al credito para mostrar generacion automatica de cuotas.
7. Cobrar una cuota parcial y revisar caja.
8. Generar PDFs: recibo, plan de pagos y contrato.
9. Exportar reportes CSV.
10. Ejecutar backup con `php artisan impacto:backup`.

## Como crear una reserva

1. Ir a Comercial > Reservas.
2. Presionar Nueva.
3. Seleccionar cliente y lote disponible.
4. Definir fecha de reserva, vencimiento y monto.
5. Guardar. El lote pasa a reservado y se registra movimiento de caja si hay monto.

## Como convertir reserva en venta

1. Ir a Comercial > Ventas.
2. Presionar Nueva.
3. Seleccionar el mismo cliente y lote reservado.
4. Completar precio, anticipo y cuotas.
5. Guardar. La reserva queda convertida y el lote pasa a vendido.

## Como registrar una venta al credito

1. Ir a Comercial > Ventas > Nueva.
2. Seleccionar cliente y lote disponible.
3. Ingresar precio final, cuota inicial y numero de cuotas.
4. Guardar. El sistema genera cuotas automaticamente.

## Como cobrar cuotas

1. Ir a Finanzas > Cuotas.
2. Ubicar la cuota pendiente, parcial o vencida.
3. Ingresar monto a cobrar y metodo de pago.
4. Guardar. Si el monto es parcial, la cuota queda parcial; si cubre el saldo, queda pagada.

## Como ver mapa de disponibilidad

1. Ir a Gestion de Terrenos > Mapa disponibilidad.
2. Filtrar por urbanizacion, estado o manzano.
3. Hacer clic en un punto para ver detalle del lote.

## Como generar PDFs

- Recibo: Finanzas > Caja > Imprimir recibo.
- Plan de pagos: Comercial > Ventas > Imprimir plan de pagos.
- Contrato: Comercial > Ventas > Generar contrato.

## Como exportar reportes

1. Ir a Reportes > Reportes.
2. Usar filtros de fecha o cliente.
3. Presionar Exportar CSV.
4. Tambien se puede exportar desde listados de lotes, clientes, ventas, cuotas, reservas y caja.

## Como hacer backup

Ejecutar:

```bash
php artisan impacto:backup
```

Los respaldos se guardan en:

```text
storage/app/backups
```
