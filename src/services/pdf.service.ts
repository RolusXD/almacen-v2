import PDFDocument from 'pdfkit';
import { ComprobanteDespacho } from '../models/types';

export class PdfService {
  /**
   * Genera el Comprobante de Entrega / Remisión de Despacho en formato PDF
   */
  static async generarComprobanteDespacho(data: ComprobanteDespacho): Promise<Buffer> {
    return new Promise((resolve, reject) => {
      try {
        const doc = new PDFDocument({
          margin: 40,
          size: 'LETTER',
          info: {
            Title: `Comprobante de Entrega - ${data.numero_remision}`,
            Author: 'Sistema de Almacén TOI',
            Subject: 'Remisión de Despacho y Entrega de Bienes',
          }
        });

        const buffers: Buffer[] = [];
        doc.on('data', (chunk) => buffers.push(chunk));
        doc.on('end', () => resolve(Buffer.concat(buffers)));
        doc.on('error', (err) => reject(err));

        const primaryColor = '#0f172a'; // Slate 900
        const accentColor = '#0284c7';  // Sky 600
        const borderColor = '#cbd5e1';  // Slate 300

        // ==========================================
        // 1. ENCABEZADO INSTITUCIONAL
        // ==========================================
        doc.rect(40, 40, 532, 70).fillAndStroke('#f8fafc', borderColor);

        doc.fillColor(primaryColor)
           .fontSize(16)
           .font('Helvetica-Bold')
           .text('TELECOMUNICACIONES OPORTUNAS INTELIGENTES (TOI)', 55, 52);

        doc.fontSize(10)
           .font('Helvetica')
           .fillColor('#64748b')
           .text('SISTEMA INTEGRADO DE GESTIÓN DE ALMACÉN Y SUMINISTROS', 55, 72)
           .text('Santa Cruz - Bolivia • Control de Inventarios y Telecomunicaciones', 55, 86);

        // Recuadro derecho: N° de Remisión
        doc.rect(410, 48, 150, 54).fillAndStroke('#ffffff', accentColor);
        doc.fillColor(accentColor)
           .fontSize(9)
           .font('Helvetica-Bold')
           .text('COMPROBANTE DE ENTREGA', 415, 56, { width: 140, align: 'center' });

        doc.fillColor('#dc2626')
           .fontSize(12)
           .font('Courier-Bold')
           .text(data.numero_remision, 415, 74, { width: 140, align: 'center' });

        doc.y = 125;

        // ==========================================
        // 2. DATOS DE LA OPERACIÓN Y DESTINO
        // ==========================================
        doc.rect(40, 125, 532, 85).fillAndStroke('#ffffff', borderColor);

        doc.fontSize(10).font('Helvetica-Bold').fillColor(primaryColor);
        doc.text('DATOS DE LA OPERACIÓN DE DESPACHO', 50, 133);
        doc.moveTo(50, 147).lineTo(560, 147).stroke(borderColor);

        doc.fontSize(9).font('Helvetica');
        // Columna 1
        doc.fillColor('#475569').text('Fecha de Entrega:', 50, 155);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.fecha_movimiento, 145, 155);

        doc.font('Helvetica').fillColor('#475569').text('Tipo Operación:', 50, 172);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.tipo_movimiento, 145, 172);

        doc.font('Helvetica').fillColor('#475569').text('Motivo / Asunto:', 50, 189);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.asunto, 145, 189);

        // Columna 2
        doc.font('Helvetica').fillColor('#475569').text('Destino / Área:', 320, 155);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.destino || 'Sin especificar', 410, 155);

        doc.font('Helvetica').fillColor('#475569').text('Receptor Asignado:', 320, 172);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.encargado, 410, 172);

        doc.font('Helvetica').fillColor('#475569').text('Despachado Por:', 320, 189);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.operador_nombre, 410, 189);

        // ==========================================
        // 3. TABLA DETALLADA DE BIENES ENTREGADOS
        // ==========================================
        const tableTop = 225;
        doc.rect(40, tableTop, 532, 22).fillAndStroke('#0f172a', '#0f172a');
        doc.fillColor('#ffffff').fontSize(8.5).font('Helvetica-Bold');
        doc.text('CÓDIGO', 45, tableTop + 6, { width: 70 });
        doc.text('DESCRIPCIÓN DEL BIEN / MATERIAL', 120, tableTop + 6, { width: 170 });
        doc.text('MODELO / MARCA', 295, tableTop + 6, { width: 90 });
        doc.text('N° SERIE / MAC / SKU', 390, tableTop + 6, { width: 110 });
        doc.text('CANTIDAD', 505, tableTop + 6, { width: 60, align: 'right' });

        // Fila de datos del ítem
        const rowTop = tableTop + 22;
        doc.rect(40, rowTop, 532, 45).fillAndStroke('#f8fafc', borderColor);

        doc.fillColor('#000000').fontSize(8.5).font('Courier');
        doc.text(data.codigo || 'S/C', 45, rowTop + 8, { width: 70 });

        doc.font('Helvetica-Bold').fontSize(9);
        doc.text(data.descripcion, 120, rowTop + 8, { width: 170 });

        doc.font('Helvetica').fontSize(8.5);
        const modeloMarca = [data.modelo, data.marca].filter(Boolean).join(' - ') || '-';
        doc.text(modeloMarca, 295, rowTop + 8, { width: 90 });

        doc.font('Courier-Bold').fontSize(8.5).fillColor(accentColor);
        doc.text(data.numero_serie_mac || 'MATERIAL LOTE', 390, rowTop + 8, { width: 110 });

        doc.font('Helvetica-Bold').fontSize(10).fillColor('#000000');
        doc.text(`${data.cantidad} ${data.unidad_medida}`, 505, rowTop + 8, { width: 60, align: 'right' });

        if (data.observaciones) {
          doc.font('Helvetica-Oblique').fontSize(8).fillColor('#64748b');
          doc.text(`Obs: ${data.observaciones}`, 45, rowTop + 28, { width: 520 });
        }

        // ==========================================
        // 4. CLÁUSULA DE CUSTODIA Y RESPONSABILIDAD
        // ==========================================
        const clausulaTop = rowTop + 60;
        doc.rect(40, clausulaTop, 532, 42).fillAndStroke('#f1f5f9', borderColor);
        doc.fillColor('#334155').fontSize(7.5).font('Helvetica-Oblique');
        doc.text(
          'DECLARACIÓN DE CUSTODIA TÉCNICA: El receptor declara haber recibido en perfecto estado de funcionamiento los bienes y suministros detallados en la presente remisión, comprometiéndose a su custodia responsable y uso estricto en las instalaciones u operaciones autorizadas por Telecomunicaciones Oportunas Inteligentes (TOI).',
          48,
          clausulaTop + 7,
          { width: 516, align: 'justify', lineGap: 1.5 }
        );

        // ==========================================
        // 5. SECCIÓN DE FIRMAS Y CONFORMIDAD
        // ==========================================
        const firmaY = clausulaTop + 90;

        // Cuadro Firma 1: Entregado por (Almacén)
        doc.rect(50, firmaY, 230, 85).stroke(borderColor);
        doc.moveTo(70, firmaY + 55).lineTo(260, firmaY + 55).stroke('#64748b');
        doc.fillColor('#000000').fontSize(8.5).font('Helvetica-Bold');
        doc.text('ENTREGADO POR (ALMACÉN)', 70, firmaY + 60, { width: 190, align: 'center' });
        doc.fontSize(7.5).font('Helvetica').fillColor('#64748b');
        doc.text(`Resp: ${data.operador_nombre}`, 70, firmaY + 72, { width: 190, align: 'center' });

        // Cuadro Firma 2: Recibido Conforme (Técnico / Responsable)
        doc.rect(330, firmaY, 230, 85).stroke(borderColor);
        doc.moveTo(350, firmaY + 55).lineTo(540, firmaY + 55).stroke('#64748b');
        doc.fillColor('#000000').fontSize(8.5).font('Helvetica-Bold');
        doc.text('RECIBIDO CONFORME (TÉCNICO / ÁREA)', 350, firmaY + 60, { width: 190, align: 'center' });
        doc.fontSize(7.5).font('Helvetica').fillColor('#64748b');
        doc.text(`Nombre: ${data.encargado}`, 350, firmaY + 72, { width: 190, align: 'center' });

        // Pie de página
        doc.fontSize(7.5).fillColor('#94a3b8').font('Helvetica');
        doc.text(
          `Documento generado electrónicamente por el Sistema de Almacén TOI • Fecha de impresión: ${new Date().toLocaleString('es-BO')}`,
          40,
          730,
          { width: 532, align: 'center' }
        );

        doc.end();
      } catch (err) {
        reject(err);
      }
    });
  }
}
