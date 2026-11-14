import { Request, Response } from 'express';
import ExcelJS from 'exceljs';
import { EquipoRepository } from '../repositories/equipo.repository';
import { AccesorioRepository } from '../repositories/accesorio.repository';
import { MovimientoRepository } from '../repositories/movimiento.repository';

const equipoRepo = new EquipoRepository();
const accesorioRepo = new AccesorioRepository();
const movimientoRepo = new MovimientoRepository();

export class ReportesController {
  static async exportarEquipos(req: Request, res: Response) {
    try {
      const equipos = await equipoRepo.getAll();
      const workbook = new ExcelJS.Workbook();
      const sheet = workbook.addWorksheet('Equipos TOI');

      sheet.columns = [
        { header: 'ID', key: 'id_equipo', width: 10 },
        { header: 'NOMBRE', key: 'nombre', width: 28 },
        { header: 'CÓDIGO', key: 'codigo', width: 14 },
        { header: 'N° SERIE / MAC', key: 'numero_serie_mac', width: 25 },
        { header: 'MODELO', key: 'modelo', width: 20 },
        { header: 'MARCA', key: 'marca', width: 18 },
        { header: 'PRECIO (Bs.)', key: 'precio', width: 15 },
        { header: 'ESTADO', key: 'estado', width: 16 },
        { header: 'CALIDAD', key: 'calidad', width: 12 },
        { header: 'PROVEEDOR', key: 'proveedor', width: 20 },
        { header: 'FECHA INGRESO', key: 'fecha_entrada', width: 16 },
      ];

      // Formato cabecera
      sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
      sheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF1E3A5F' },
      };

      equipos.forEach(eq => {
        sheet.addRow({
          ...eq,
          fecha_entrada: eq.fecha_entrada ? new Date(eq.fecha_entrada).toISOString().split('T')[0] : '',
        });
      });

      res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      res.setHeader('Content-Disposition', `attachment; filename=Equipos_TOI_${new Date().toISOString().split('T')[0]}.xlsx`);

      await workbook.xlsx.write(res);
      res.end();
    } catch (err) {
      console.error('Error exportando equipos:', err);
      res.status(500).send('Error generando archivo Excel');
    }
  }

  static async exportarAccesorios(req: Request, res: Response) {
    try {
      const accesorios = await accesorioRepo.getAll();
      const workbook = new ExcelJS.Workbook();
      const sheet = workbook.addWorksheet('Accesorios TOI');

      sheet.columns = [
        { header: 'ID', key: 'id_accesorio', width: 10 },
        { header: 'CÓDIGO', key: 'codigo', width: 15 },
        { header: 'DESCRIPCIÓN / NOMBRE', key: 'nombre', width: 30 },
        { header: 'CANTIDAD', key: 'cantidad', width: 14 },
        { header: 'UNIDAD', key: 'unidad_medida', width: 14 },
        { header: 'COSTO UNIT. (Bs.)', key: 'costo', width: 18 },
        { header: 'CALIDAD', key: 'calidad', width: 12 },
        { header: 'PROVEEDOR', key: 'nombre_proveedor', width: 22 },
      ];

      sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
      sheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF1E3A5F' },
      };

      accesorios.forEach(a => sheet.addRow(a));

      res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      res.setHeader('Content-Disposition', `attachment; filename=Accesorios_TOI_${new Date().toISOString().split('T')[0]}.xlsx`);

      await workbook.xlsx.write(res);
      res.end();
    } catch (err) {
      console.error('Error exportando accesorios:', err);
      res.status(500).send('Error generando archivo Excel');
    }
  }

  static async exportarMovimientosEquipos(req: Request, res: Response) {
    try {
      const movimientos = await movimientoRepo.getMovimientosEquipos(undefined, undefined, undefined, 2000);
      const workbook = new ExcelJS.Workbook();
      const sheet = workbook.addWorksheet('Movimientos Equipos');

      sheet.columns = [
        { header: 'FECHA', key: 'fecha_movimiento', width: 14 },
        { header: 'TIPO', key: 'tipo_movimiento', width: 14 },
        { header: 'N° SERIE / MAC', key: 'numero_serie_mac', width: 24 },
        { header: 'PRODUCTO', key: 'nombre_equipo', width: 26 },
        { header: 'MODELO', key: 'modelo_equipo', width: 18 },
        { header: 'DESTINO', key: 'destino', width: 22 },
        { header: 'ENCARGADO', key: 'encargado', width: 22 },
        { header: 'ASUNTO', key: 'asunto', width: 20 },
        { header: 'OBSERVACIONES', key: 'observaciones', width: 30 },
      ];

      sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
      sheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF1E3A5F' },
      };

      movimientos.forEach(m => {
        sheet.addRow({
          ...m,
          fecha_movimiento: m.fecha_movimiento ? new Date(m.fecha_movimiento).toISOString().split('T')[0] : '',
        });
      });

      res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      res.setHeader('Content-Disposition', `attachment; filename=Movimientos_Equipos_TOI_${new Date().toISOString().split('T')[0]}.xlsx`);

      await workbook.xlsx.write(res);
      res.end();
    } catch (err) {
      console.error('Error exportando movimientos equipos:', err);
      res.status(500).send('Error generando archivo Excel');
    }
  }

  static async exportarMovimientosAccesorios(req: Request, res: Response) {
    try {
      const movimientos = await movimientoRepo.getMovimientosAccesorios(undefined, undefined, 2000);
      const workbook = new ExcelJS.Workbook();
      const sheet = workbook.addWorksheet('Movimientos Accesorios');

      sheet.columns = [
        { header: 'FECHA', key: 'fecha_movimiento', width: 14 },
        { header: 'TIPO', key: 'tipo_movimiento', width: 14 },
        { header: 'MATERIAL', key: 'nombre_accesorio', width: 28 },
        { header: 'CANTIDAD', key: 'cantidad', width: 14 },
        { header: 'UNIDAD', key: 'unidad_medida', width: 14 },
        { header: 'DESTINO', key: 'destino', width: 22 },
        { header: 'ENCARGADO', key: 'encargado', width: 22 },
        { header: 'ASUNTO', key: 'asunto', width: 20 },
        { header: 'OBSERVACIONES', key: 'observaciones', width: 30 },
      ];

      sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
      sheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF1E3A5F' },
      };

      movimientos.forEach(m => {
        sheet.addRow({
          ...m,
          fecha_movimiento: m.fecha_movimiento ? new Date(m.fecha_movimiento).toISOString().split('T')[0] : '',
        });
      });

      res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      res.setHeader('Content-Disposition', `attachment; filename=Movimientos_Accesorios_TOI_${new Date().toISOString().split('T')[0]}.xlsx`);

      await workbook.xlsx.write(res);
      res.end();
    } catch (err) {
      console.error('Error exportando movimientos accesorios:', err);
      res.status(500).send('Error generando archivo Excel');
    }
  }
}
