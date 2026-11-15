import PDFDocument from 'pdfkit';
import { ComprobanteDespacho, ComprobanteDespachoUnificado } from '../models/types';

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

  /**
   * Genera el Comprobante de Despacho Unificado (Equipos + Accesorios) en PDF
   */
  static async generarComprobanteDespachoUnificado(data: ComprobanteDespachoUnificado): Promise<Buffer> {
    return new Promise((resolve, reject) => {
      try {
        const doc = new PDFDocument({
          margin: 40,
          size: 'LETTER',
          info: {
            Title: `Orden de Despacho - ${data.numero_orden}`,
            Author: data.empresa_nombre || 'Almacén TOI',
            Subject: 'Orden de Despacho y Custodia de Materiales y Equipos',
          }
        });

        const buffers: Buffer[] = [];
        doc.on('data', (chunk) => buffers.push(chunk));
        doc.on('end', () => resolve(Buffer.concat(buffers)));
        doc.on('error', (err) => reject(err));

        const primaryColor = '#0f172a'; // Slate 900
        const accentColor = '#059669';  // Emerald 600
        const borderColor = '#cbd5e1';  // Slate 300

        // ==========================================
        // 1. ENCABEZADO INSTITUCIONAL DINÁMICO
        // ==========================================
        doc.rect(40, 40, 532, 70).fillAndStroke('#f8fafc', borderColor);

        doc.fillColor(primaryColor)
           .fontSize(14)
           .font('Helvetica-Bold')
           .text(data.empresa_nombre || 'TELECOMUNICACIONES OPORTUNAS INTELIGENTES (TOI)', 55, 50, { width: 340 });

        doc.fontSize(8.5)
           .font('Helvetica')
           .fillColor('#64748b')
           .text(`NIT: ${data.empresa_nit || '1029384021'} • Tel: ${data.empresa_telefono || '+591 2 2456789'}`, 55, 68)
           .text(data.empresa_direccion || 'Dirección de Almacén Central', 55, 80, { width: 340 })
           .text('SISTEMA INTEGRADO DE GESTIÓN Y CUSTODIA DE ALMACÉN', 55, 92);

        // Recuadro derecho: N° de Orden
        doc.rect(410, 48, 150, 54).fillAndStroke('#ffffff', accentColor);
        doc.fillColor(accentColor)
           .fontSize(8.5)
           .font('Helvetica-Bold')
           .text('ORDEN DE DESPACHO', 415, 55, { width: 140, align: 'center' });

        doc.fillColor('#059669')
           .fontSize(11)
           .font('Courier-Bold')
           .text(data.numero_orden, 415, 72, { width: 140, align: 'center' });

        // ==========================================
        // 2. DATOS GENERALES DE LA ORDEN
        // ==========================================
        doc.rect(40, 120, 532, 65).fillAndStroke('#ffffff', borderColor);

        doc.fontSize(9.5).font('Helvetica-Bold').fillColor(primaryColor);
        doc.text('DATOS DE LA ORDEN DE DESPACHO', 50, 127);
        doc.moveTo(50, 139).lineTo(560, 139).stroke(borderColor);

        doc.fontSize(8.5).font('Helvetica');
        // Fila 1
        doc.fillColor('#475569').text('Fecha / Hora:', 50, 145);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.fecha_despacho, 120, 145);

        doc.font('Helvetica').fillColor('#475569').text('Destino / Proyecto:', 280, 145);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.destino, 380, 145, { width: 180 });

        // Fila 2
        doc.font('Helvetica').fillColor('#475569').text('Receptor:', 50, 160);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.encargado_receptor, 120, 160, { width: 150 });

        doc.font('Helvetica').fillColor('#475569').text('Operador Almacén:', 280, 160);
        doc.fillColor('#000000').font('Helvetica-Bold').text(data.operador_nombre, 380, 160, { width: 180 });

        // Fila 3
        doc.font('Helvetica').fillColor('#475569').text('Asunto / Motivo:', 50, 173);
        doc.fillColor('#000000').font('Helvetica').text(data.asunto, 120, 173, { width: 440 });

        let currentY = 195;

        // ==========================================
        // 3. TABLA DE EQUIPOS DESPACHADOS (Si hay)
        // ==========================================
        if (data.equipos && data.equipos.length > 0) {
          doc.rect(40, currentY, 532, 20).fill('#f1f5f9');
          doc.fillColor(primaryColor).fontSize(9).font('Helvetica-Bold');
          doc.text(`1. EQUIPOS DE RED Y TELECOMUNICACIONES (${data.equipos.length})`, 50, currentY + 5);

          currentY += 24;
          // Cabecera tabla equipos
          doc.rect(40, currentY, 532, 18).fillAndStroke('#e2e8f0', borderColor);
          doc.fontSize(8).font('Helvetica-Bold').fillColor('#334155');
          doc.text('#', 45, currentY + 5, { width: 25, align: 'center' });
          doc.text('CÓDIGO', 75, currentY + 5, { width: 70 });
          doc.text('MARCA / MODELO', 150, currentY + 5, { width: 120 });
          doc.text('DESCRIPCIÓN DEL EQUIPO', 275, currentY + 5, { width: 140 });
          doc.text('N° SERIE / MAC', 420, currentY + 5, { width: 145 });

          currentY += 18;

          data.equipos.forEach((eq, index) => {
            const isEven = index % 2 === 0;
            if (isEven) {
              doc.rect(40, currentY, 532, 16).fill('#f8fafc');
            }
            doc.rect(40, currentY, 532, 16).stroke(borderColor);

            doc.fontSize(8).font('Helvetica').fillColor('#000000');
            doc.text(String(index + 1), 45, currentY + 4, { width: 25, align: 'center' });
            doc.text(eq.codigo || '-', 75, currentY + 4, { width: 70 });
            doc.text(`${eq.marca || ''} ${eq.modelo || ''}`.trim() || '-', 150, currentY + 4, { width: 120 });
            doc.font('Helvetica-Bold').text(eq.descripcion, 275, currentY + 4, { width: 140 });
            doc.font('Courier-Bold').fillColor('#0369a1').text(eq.numero_serie_mac, 420, currentY + 4, { width: 145 });

            currentY += 16;
          });

          currentY += 10;
        }

        // ==========================================
        // 4. TABLA DE MATERIALES Y ACCESORIOS (Si hay)
        // ==========================================
        if (data.accesorios && data.accesorios.length > 0) {
          doc.rect(40, currentY, 532, 20).fill('#f1f5f9');
          doc.fillColor(primaryColor).fontSize(9).font('Helvetica-Bold');
          doc.text(`2. MATERIALES, FIBRA Y ACCESORIOS ENTREGADOS (${data.accesorios.length})`, 50, currentY + 5);

          currentY += 24;
          // Cabecera tabla accesorios
          doc.rect(40, currentY, 532, 18).fillAndStroke('#e2e8f0', borderColor);
          doc.fontSize(8).font('Helvetica-Bold').fillColor('#334155');
          doc.text('#', 45, currentY + 5, { width: 25, align: 'center' });
          doc.text('CÓDIGO', 75, currentY + 5, { width: 80 });
          doc.text('DESCRIPCIÓN DEL MATERIAL', 160, currentY + 5, { width: 220 });
          doc.text('CANTIDAD', 390, currentY + 5, { width: 80, align: 'right' });
          doc.text('UNIDAD', 485, currentY + 5, { width: 80 });

          currentY += 18;

          data.accesorios.forEach((acc, index) => {
            const isEven = index % 2 === 0;
            if (isEven) {
              doc.rect(40, currentY, 532, 16).fill('#f8fafc');
            }
            doc.rect(40, currentY, 532, 16).stroke(borderColor);

            doc.fontSize(8).font('Helvetica').fillColor('#000000');
            doc.text(String(index + 1), 45, currentY + 4, { width: 25, align: 'center' });
            doc.text(acc.codigo || '-', 75, currentY + 4, { width: 80 });
            doc.font('Helvetica-Bold').text(acc.descripcion, 160, currentY + 4, { width: 220 });
            doc.font('Helvetica-Bold').fillColor('#059669').text(String(acc.cantidad), 390, currentY + 4, { width: 80, align: 'right' });
            doc.font('Helvetica').fillColor('#475569').text(acc.unidad_medida, 485, currentY + 4, { width: 80 });

            currentY += 16;
          });

          currentY += 10;
        }

        // ==========================================
        // 5. OBSERVACIONES Y RECUADROS DE FIRMAS
        // ==========================================
        const firmaY = Math.max(currentY + 15, 610);

        // Pie legal
        doc.fontSize(7.5).font('Helvetica-Oblique').fillColor('#64748b');
        doc.text(
          data.almacen_pie_firma || 'Documento oficial de entrega y custodia de materiales de red y telecomunicaciones. El receptor asume total responsabilidad del inventario asignado.',
          40,
          firmaY - 20,
          { width: 532, align: 'center' }
        );

        // Cuadro Firma 1: Entregado por (Almacén)
        doc.rect(40, firmaY, 240, 75).stroke(borderColor);
        doc.moveTo(60, firmaY + 50).lineTo(260, firmaY + 50).stroke('#64748b');
        doc.fillColor('#000000').fontSize(8.5).font('Helvetica-Bold');
        doc.text('ENTREGADO POR (ALMACÉN CENTRAL)', 60, firmaY + 54, { width: 200, align: 'center' });
        doc.fontSize(7.5).font('Helvetica').fillColor('#64748b');
        doc.text(`Firma: ${data.operador_nombre}`, 60, firmaY + 65, { width: 200, align: 'center' });

        // Cuadro Firma 2: Recibido Conforme (Cuadrilla / Receptor)
        doc.rect(332, firmaY, 240, 75).stroke(borderColor);
        doc.moveTo(352, firmaY + 50).lineTo(552, firmaY + 50).stroke('#64748b');
        doc.fillColor('#000000').fontSize(8.5).font('Helvetica-Bold');
        doc.text('RECIBIDO CONFORME (CUADRILLA / PROYECTO)', 352, firmaY + 54, { width: 200, align: 'center' });
        doc.fontSize(7.5).font('Helvetica').fillColor('#64748b');
        doc.text(`Firma: ${data.encargado_receptor}`, 352, firmaY + 65, { width: 200, align: 'center' });

        // Pie de página con timestamp de impresión
        doc.fontSize(7).fillColor('#94a3b8').font('Helvetica');
        doc.text(
          `Comprobante Electrónico • Almacén TOI v4.0 • Generado: ${new Date().toLocaleString('es-BO')}`,
          40,
          735,
          { width: 532, align: 'center' }
        );

        doc.end();
      } catch (err) {
        reject(err);
      }
    });
  }
}
