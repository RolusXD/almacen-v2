export interface Usuario {
  id_usuario: number;
  nombre_completo: string;
  usuario: string;
  password_hash: string;
  carnet: string;
  email: string;
  fecha_nacimiento?: string;
  rol: 'admin' | 'usuario';
  activo: number;
  ultimo_acceso?: string;
  created_at: string;
  updated_at: string;
}

export interface Equipo {
  id_equipo: number;
  id_tipo_equipo?: number | null;
  id_categoria?: number | null;
  id_marca?: number | null;
  id_proveedor?: number | null;
  codigo?: string | null;
  nombre: string;
  marca?: string | null;
  modelo?: string | null;
  numero_serie_mac: string;
  precio?: number | null;
  costo?: number | null;
  proveedor?: string | null;
  fecha_entrada?: string | null;
  estado: 'DISPONIBLE' | 'OCUPADO' | 'MANTENIMIENTO' | 'RETIRADO' | string;
  calidad: 'NUEVO' | 'BUENO' | 'REGULAR' | 'MALO' | string;
  observaciones?: string | null;
  created_at?: string;
  updated_at?: string;
  // Joins
  tipo_descripcion?: string;
  tipo_categoria?: string;
}

export interface Accesorio {
  id_accesorio: number;
  id_categoria?: number | null;
  id_marca?: number | null;
  id_proveedor?: number | null;
  codigo?: string | null;
  nombre: string;
  cantidad: number;
  unidad_medida: string;
  costo?: number | null;
  modelo?: string | null;
  marca?: string | null;
  numero_serie_mac?: string | null;
  fecha_entrada: string;
  calidad: 'NUEVO' | 'BUENO' | 'REGULAR' | 'MALO' | string;
  estado: 'DISPONIBLE' | 'OCUPADO' | 'MANTENIMIENTO' | 'RETIRADO' | string;
  observaciones?: string | null;
  created_at?: string;
  updated_at?: string;
  // Joins
  nombre_proveedor?: string;
  nombre_categoria?: string;
}

export interface MovimientoEquipo {
  id_movimiento: number;
  id_equipo?: number | null;
  numero_serie_mac: string;
  tipo_movimiento: 'SALIDA' | 'ENTRADA' | 'MANTENIMIENTO' | 'REVISION';
  asunto: string;
  encargado: string;
  destino?: string | null;
  observaciones?: string | null;
  observaciones_detalladas?: string | null;
  fecha_movimiento: string;
  created_by?: number | null;
  created_at?: string;
  // Joins
  nombre_equipo?: string;
  codigo_equipo?: string;
  marca_equipo?: string;
  modelo_equipo?: string;
  usuario_registro?: string;
}

export interface MovimientoAccesorio {
  id_movimiento: number;
  id_accesorio: number;
  tipo_movimiento: 'SALIDA' | 'ENTRADA' | 'MANTENIMIENTO' | 'REVISION';
  cantidad: number;
  asunto: string;
  encargado: string;
  destino?: string | null;
  observaciones?: string | null;
  observaciones_detalladas?: string | null;
  fecha_movimiento: string;
  created_by?: number | null;
  created_at?: string;
  // Joins
  nombre_accesorio?: string;
  codigo_accesorio?: string;
  unidad_medida?: string;
  usuario_registro?: string;
}

export interface TipoEquipo {
  id_codigo: number;
  codigo: string;
  prefijo?: string | null;
  descripcion: string;
  categoria: 'ROUTER' | 'ONU' | 'SWITCH' | 'OTRO';
  observaciones?: string | null;
  activo: number;
  created_at?: string;
  updated_at?: string;
}

export interface Proveedor {
  id_proveedor: number;
  nombre_empresa: string;
  contacto?: string | null;
  telefono?: string | null;
  email?: string | null;
  direccion?: string | null;
  nit?: string | null;
  pagina_web?: string | null;
  activo: number;
  created_at?: string;
  updated_at?: string;
}

export interface Categoria {
  id_categoria: number;
  nombre: string;
  descripcion?: string | null;
  activo: number;
  created_at?: string;
  updated_at?: string;
  total_equipos?: number;
  total_accesorios?: number;
}

export interface Marca {
  id_marca: number;
  nombre: string;
  pais_origen?: string | null;
  pagina_web?: string | null;
  activo: number;
  created_at?: string;
  updated_at?: string;
  total_equipos?: number;
  total_accesorios?: number;
}

export interface Mantenimiento {
  id_mantenimiento: number;
  id_equipo?: number | null;
  numero_serie_mac: string;
  tipo_mantenimiento: 'PREVENTIVO' | 'CORRECTIVO' | 'EVOLUTIVO';
  descripcion: string;
  tecnico_responsable: string;
  costo?: number | null;
  estado: 'EN_PROCESO' | 'COMPLETADO' | 'CANCELADO';
  fecha_ingreso: string;
  fecha_entrega?: string | null;
  repuestos_cambiados?: string | null;
  observaciones?: string | null;
  garantia_dias?: number;
  created_by?: number | null;
  created_at?: string;
  updated_at?: string;
  // Joins
  nombre_equipo?: string;
}

export interface ConfiguracionSmtp {
  id?: number;
  host: string;
  puerto: number;
  usuario?: string | null;
  password_cifrado?: string | null;
  secure: boolean;
  remitente_email: string;
  remitente_nombre: string;
  activo: boolean;
  updated_at?: string;
}



export interface Prestamo {
  id_prestamo: number;
  id_equipo?: number | null;
  numero_serie_mac: string;
  id_usuario_solicita?: number | null;
  nombre_solicita: string;
  cargo_solicita?: string | null;
  departamento?: string | null;
  telefono_contacto?: string | null;
  email_contacto?: string | null;
  fecha_prestamo: string;
  fecha_devolucion_esperada: string;
  fecha_devolucion_real?: string | null;
  estado: 'PRESTADO' | 'DEVUELTO' | 'ATRASADO' | 'PERDIDO';
  motivo_prestamo: string;
  observaciones_salida?: string | null;
  observaciones_devolucion?: string | null;
  equipo_entregado_conforme?: number;
  equipo_recibido_conforme?: number;
  created_by?: number | null;
  created_at?: string;
  updated_at?: string;
  // Joins
  nombre_equipo?: string;
}

export interface ConfiguracionSmtp {
  id?: number;
  host: string;
  puerto: number;
  usuario?: string | null;
  password_cifrado?: string | null;
  secure: boolean;
  remitente_email: string;
  remitente_nombre: string;
  activo: boolean;
  updated_at?: string;
}



export interface ComprobanteDespacho {
  id_movimiento: number;
  tipo_item: 'EQUIPO' | 'ACCESORIO';
  numero_remision: string;
  fecha_movimiento: string;
  tipo_movimiento: string;
  asunto: string;
  encargado: string;
  destino: string;
  observaciones?: string | null;
  operador_nombre: string;
  operador_carnet?: string;
  descripcion: string;
  codigo?: string | null;
  marca?: string | null;
  modelo?: string | null;
  numero_serie_mac?: string | null;
  cantidad: number;
  unidad_medida: string;
}

// ==========================================
// SPEC-004: DESPACHO GENERAL Y ADMINISTRACIÓN
// ==========================================

export interface OrdenDespacho {
  id_orden: number;
  numero_orden: string;
  fecha_despacho: string;
  destino: string;
  encargado_receptor: string;
  asunto: string;
  observaciones?: string | null;
  estado: 'COMPLETADO' | 'ANULADO' | string;
  created_by?: number | null;
  created_at?: string;
  updated_at?: string;
  // Joins y métricas
  operador_nombre?: string;
  operador_carnet?: string;
  total_items?: number;
  total_equipos?: number;
  total_accesorios?: number;
  detalles?: OrdenDespachoDetalle[];
}

export interface OrdenDespachoDetalle {
  id_detalle: number;
  id_orden: number;
  tipo_item: 'EQUIPO' | 'ACCESORIO';
  id_item?: number | null;
  numero_serie_mac?: string | null;
  descripcion: string;
  codigo?: string | null;
  marca?: string | null;
  modelo?: string | null;
  cantidad: number;
  unidad_medida: string;
  created_at?: string;
}

export interface ItemCarritoDespacho {
  tipo_item: 'EQUIPO' | 'ACCESORIO';
  id_item?: number;
  id_equipo?: number;
  id_accesorio?: number;
  numero_serie_mac?: string;
  descripcion: string;
  codigo?: string;
  marca?: string;
  modelo?: string;
  cantidad: number;
  unidad_medida?: string;
  stock_disponible?: number;
}

export interface ConfiguracionGeneral {
  id: number;
  zona_horaria: string;
  empresa_nombre: string;
  empresa_nit?: string | null;
  empresa_direccion?: string | null;
  empresa_telefono?: string | null;
  empresa_email?: string | null;
  almacen_pie_firma?: string | null;
  stock_minimo_defecto: number;
  alertas_emails?: string | null;
  updated_at?: string;
}

export interface AuditoriaLog {
  id_log: number;
  fecha_hora: string;
  id_usuario?: number | null;
  nombre_usuario: string;
  rol_usuario: string;
  ip: string;
  modulo: string;
  accion: string;
  detalle?: string | null;
  created_at?: string;
}

export interface ComprobanteDespachoUnificado {
  id_orden: number;
  numero_orden: string;
  fecha_despacho: string;
  destino: string;
  encargado_receptor: string;
  asunto: string;
  observaciones?: string | null;
  operador_nombre: string;
  operador_carnet?: string;
  empresa_nombre: string;
  empresa_nit?: string;
  empresa_direccion?: string;
  empresa_telefono?: string;
  almacen_pie_firma?: string;
  equipos: {
    numero_serie_mac: string;
    descripcion: string;
    codigo?: string;
    marca?: string;
    modelo?: string;
    cantidad: number;
  }[];
  accesorios: {
    descripcion: string;
    codigo?: string;
    marca?: string;
    cantidad: number;
    unidad_medida: string;
  }[];
}
