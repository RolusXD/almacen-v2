import ExcelJS from 'exceljs';

export interface ReporteItemRow {
  fecha: string;
  tipo: string;
  codigo: string;
  descripcion: string;
  serie_mac: string;
  cantidad: number;
  unidad: string;
  destino: string;
  encargado: string;
  asunto: string;
  operador: string;
  observaciones: string;
}

export class ExcelService {
  /**
   * Genera un archivo .xlsx nativo con estilos profesionales
   */
  static async generarReporteMovimientos(items: ReporteItemRow[], titulo: string = 'Reporte de Movimientos'): Promise<Buffer> {
    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Sistema de Almacén TOI';
    workbook.created = new Date();

    const sheet = workbook.addWorksheet('Movimientos', {
      views: [{ showGridLines: true }]
    });

    // 1. Título del Reporte
    sheet.mergeCells('A1:L1');
    const titleCell = sheet.getCell('A1');
    titleCell.value = `ALMACÉN TOI - ${titulo.toUpperCase()}`;
    titleCell.font = { name: 'Arial', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
    titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } };
    titleCell.alignment = { vertical: 'middle', horizontal: 'center' };
    sheet.getRow(1).height = 32;

    // Subtítulo con fecha
    sheet.mergeCells('A2:L2');
    const subCell = sheet.getCell('A2');
    subCell.value = `Fecha de Emisión: ${new Date().toLocaleString('es-BO')} • Total de Registros: ${items.length}`;
    subCell.font = { name: 'Arial', size: 9, italic: true, color: { argb: 'FF64748B' } };
    subCell.alignment = { vertical: 'middle', horizontal: 'center' };
    sheet.getRow(2).height = 20;

    // Fila vacía
    sheet.getRow(3).height = 10;

    // 2. Encabezados de Columna
    const headers = [
      { header: 'FECHA', key: 'fecha', width: 14 },
      { header: 'TIPO', key: 'tipo', width: 14 },
      { header: 'CÓDIGO', key: 'codigo', width: 14 },
      { header: 'DESCRIPCIÓN DEL ÍTEM', key: 'descripcion', width: 28 },
      { header: 'SERIE / MAC / SKU', key: 'serie_mac', width: 24 },
      { header: 'CANT.', key: 'cantidad', width: 10 },
      { header: 'UNIDAD', key: 'unidad', width: 12 },
      { header: 'DESTINO / ÁREA', key: 'destino', width: 22 },
      { header: 'ENCARGADO / RECEPTOR', key: 'encargado', width: 24 },
      { header: 'ASUNTO', key: 'asunto', width: 18 },
      { header: 'OPERADOR ALMACÉN', key: 'operador', width: 22 },
      { header: 'OBSERVACIONES', key: 'observaciones', width: 30 },
    ];

    sheet.getRow(4).values = headers.map(h => h.header);
    sheet.getRow(4).height = 24;

    // Estilo de encabezados
    sheet.getRow(4).eachCell((cell) => {
      cell.font = { name: 'Arial', size: 9, bold: true, color: { argb: 'FFFFFFFF' } };
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E293B' } };
      cell.alignment = { vertical: 'middle', horizontal: 'center' };
      cell.border = {
        top: { style: 'thin', color: { argb: 'FFCBD5E1' } },
        bottom: { style: 'medium', color: { argb: 'FF0F172A' } },
      };
    });

    // 3. Filas de datos
    headers.forEach((h, idx) => {
      sheet.getColumn(idx + 1).width = h.width;
    });

    items.forEach((item, index) => {
      const row = sheet.addRow([
        item.fecha,
        item.tipo,
        item.codigo || '-',
        item.descripcion,
        item.serie_mac || '-',
        item.cantidad,
        item.unidad,
        item.destino || '-',
        item.encargado,
        item.asunto,
        item.operador,
        item.observaciones || '-'
      ]);

      const isEven = index % 2 === 0;
      row.height = 20;

      row.eachCell((cell, colNumber) => {
        cell.font = { name: 'Arial', size: 9, color: { argb: 'FF000000' } };
        cell.alignment = { vertical: 'middle' };
        if (isEven) {
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
        }

        // Alineación específica por columna
        if (colNumber === 1 || colNumber === 2 || colNumber === 7) {
          cell.alignment = { vertical: 'middle', horizontal: 'center' };
        } else if (colNumber === 6) {
          cell.alignment = { vertical: 'middle', horizontal: 'right' };
          cell.numFmt = '#,##0';
        } else if (colNumber === 3 || colNumber === 5) {
          cell.font = { name: 'Consolas', size: 9, color: { argb: 'FF0369A1' } };
        }

        cell.border = {
          bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } }
        };
      });
    });

    const buffer = await workbook.xlsx.writeBuffer();
    return Buffer.from(buffer);
  }
}
