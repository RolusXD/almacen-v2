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
