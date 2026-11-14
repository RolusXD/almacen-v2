--
-- PostgreSQL database dump
--

\restrict CF0Qj6dIqWiIp4eAFltPC0ZvZYm2pqIAYPauI4spI4UzpeJgBLc7cWcoJW3cenR

-- Dumped from database version 17.8 (a48d9ca)
-- Dumped by pg_dump version 18.3

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.prestamos DROP CONSTRAINT IF EXISTS prestamos_numero_serie_mac_fkey;
ALTER TABLE IF EXISTS ONLY public.prestamos DROP CONSTRAINT IF EXISTS prestamos_id_usuario_solicita_fkey;
ALTER TABLE IF EXISTS ONLY public.prestamos DROP CONSTRAINT IF EXISTS prestamos_id_usuario_recibe_fkey;
ALTER TABLE IF EXISTS ONLY public.prestamos DROP CONSTRAINT IF EXISTS prestamos_id_usuario_entrega_fkey;
ALTER TABLE IF EXISTS ONLY public.prestamos DROP CONSTRAINT IF EXISTS prestamos_id_equipo_fkey;
ALTER TABLE IF EXISTS ONLY public.prestamos DROP CONSTRAINT IF EXISTS prestamos_created_by_fkey;
ALTER TABLE IF EXISTS ONLY public.movimientos_equipos DROP CONSTRAINT IF EXISTS movimientos_equipos_numero_serie_mac_fkey;
ALTER TABLE IF EXISTS ONLY public.movimientos_equipos DROP CONSTRAINT IF EXISTS movimientos_equipos_id_equipo_fkey;
ALTER TABLE IF EXISTS ONLY public.movimientos_equipos DROP CONSTRAINT IF EXISTS movimientos_equipos_created_by_fkey;
ALTER TABLE IF EXISTS ONLY public.movimientos_accesorios DROP CONSTRAINT IF EXISTS movimientos_accesorios_id_accesorio_fkey;
ALTER TABLE IF EXISTS ONLY public.movimientos_accesorios DROP CONSTRAINT IF EXISTS movimientos_accesorios_created_by_fkey;
ALTER TABLE IF EXISTS ONLY public.mantenimientos DROP CONSTRAINT IF EXISTS mantenimientos_numero_serie_mac_fkey;
ALTER TABLE IF EXISTS ONLY public.mantenimientos DROP CONSTRAINT IF EXISTS mantenimientos_id_equipo_fkey;
ALTER TABLE IF EXISTS ONLY public.mantenimientos DROP CONSTRAINT IF EXISTS mantenimientos_created_by_fkey;
ALTER TABLE IF EXISTS ONLY public.equipos DROP CONSTRAINT IF EXISTS equipos_id_tipo_equipo_fkey;
ALTER TABLE IF EXISTS ONLY public.equipos DROP CONSTRAINT IF EXISTS equipos_id_proveedor_fkey;
ALTER TABLE IF EXISTS ONLY public.equipos DROP CONSTRAINT IF EXISTS equipos_id_marca_fkey;
ALTER TABLE IF EXISTS ONLY public.equipos DROP CONSTRAINT IF EXISTS equipos_id_categoria_fkey;
ALTER TABLE IF EXISTS ONLY public.equipos DROP CONSTRAINT IF EXISTS equipos_codigo_fkey;
ALTER TABLE IF EXISTS ONLY public.accesorios DROP CONSTRAINT IF EXISTS accesorios_id_proveedor_fkey;
ALTER TABLE IF EXISTS ONLY public.accesorios DROP CONSTRAINT IF EXISTS accesorios_id_marca_fkey;
ALTER TABLE IF EXISTS ONLY public.accesorios DROP CONSTRAINT IF EXISTS accesorios_id_categoria_fkey;
DROP TRIGGER IF EXISTS trg_usuarios_updated_at ON public.usuarios;
DROP TRIGGER IF EXISTS trg_tipos_equipos_updated_at ON public.tipos_equipos;
DROP TRIGGER IF EXISTS trg_proveedores_updated_at ON public.proveedores;
DROP TRIGGER IF EXISTS trg_prestamos_updated_at ON public.prestamos;
DROP TRIGGER IF EXISTS trg_prestamo_update ON public.prestamos;
DROP TRIGGER IF EXISTS trg_prestamo_insert ON public.prestamos;
DROP TRIGGER IF EXISTS trg_movimiento_equipo_insert ON public.movimientos_equipos;
DROP TRIGGER IF EXISTS trg_movimiento_accesorio_insert ON public.movimientos_accesorios;
DROP TRIGGER IF EXISTS trg_marcas_updated_at ON public.marcas;
DROP TRIGGER IF EXISTS trg_mantenimientos_updated_at ON public.mantenimientos;
DROP TRIGGER IF EXISTS trg_equipos_updated_at ON public.equipos;
DROP TRIGGER IF EXISTS trg_categorias_updated_at ON public.categorias;
DROP TRIGGER IF EXISTS trg_categorias_general_updated_at ON public.categorias_general;
DROP TRIGGER IF EXISTS trg_accesorios_updated_at ON public.accesorios;
DROP INDEX IF EXISTS public.idx_movimientos_equipos_fecha;
DROP INDEX IF EXISTS public.idx_movimientos_accesorios_fecha;
DROP INDEX IF EXISTS public.idx_equipos_estado;
DROP INDEX IF EXISTS public.idx_equipos_codigo;
DROP INDEX IF EXISTS public.idx_app_sessions_last_activity;
DROP INDEX IF EXISTS public.idx_accesorios_codigo;
ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_usuario_key;
ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_pkey;
ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_email_key;
ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_carnet_key;
ALTER TABLE IF EXISTS ONLY public.unidades_medida DROP CONSTRAINT IF EXISTS unidades_medida_pkey;
ALTER TABLE IF EXISTS ONLY public.tipos_equipos DROP CONSTRAINT IF EXISTS tipos_equipos_pkey;
ALTER TABLE IF EXISTS ONLY public.tipos_equipos DROP CONSTRAINT IF EXISTS tipos_equipos_codigo_key;
ALTER TABLE IF EXISTS ONLY public.tipos_accesorios DROP CONSTRAINT IF EXISTS tipos_accesorios_pkey;
ALTER TABLE IF EXISTS ONLY public.tipos_accesorios DROP CONSTRAINT IF EXISTS tipos_accesorios_codigo_key;
ALTER TABLE IF EXISTS ONLY public.proveedores DROP CONSTRAINT IF EXISTS proveedores_pkey;
ALTER TABLE IF EXISTS ONLY public.prestamos DROP CONSTRAINT IF EXISTS prestamos_pkey;
ALTER TABLE IF EXISTS ONLY public.movimientos_equipos DROP CONSTRAINT IF EXISTS movimientos_equipos_pkey;
ALTER TABLE IF EXISTS ONLY public.movimientos_accesorios DROP CONSTRAINT IF EXISTS movimientos_accesorios_pkey;
ALTER TABLE IF EXISTS ONLY public.marcas DROP CONSTRAINT IF EXISTS marcas_pkey;
ALTER TABLE IF EXISTS ONLY public.mantenimientos DROP CONSTRAINT IF EXISTS mantenimientos_pkey;
ALTER TABLE IF EXISTS ONLY public.equipos DROP CONSTRAINT IF EXISTS equipos_pkey;
ALTER TABLE IF EXISTS ONLY public.equipos DROP CONSTRAINT IF EXISTS equipos_numero_serie_mac_key;
ALTER TABLE IF EXISTS ONLY public.destinos DROP CONSTRAINT IF EXISTS destinos_pkey;
ALTER TABLE IF EXISTS ONLY public.categorias DROP CONSTRAINT IF EXISTS categorias_pkey;
ALTER TABLE IF EXISTS ONLY public.categorias_general DROP CONSTRAINT IF EXISTS categorias_general_pkey;
ALTER TABLE IF EXISTS ONLY public.app_sessions DROP CONSTRAINT IF EXISTS app_sessions_pkey;
ALTER TABLE IF EXISTS ONLY public.accesorios DROP CONSTRAINT IF EXISTS accesorios_pkey;
DROP VIEW IF EXISTS public.vista_resumen_movimientos_mes;
DROP VIEW IF EXISTS public.vista_resumen_equipos_por_codigo;
DROP VIEW IF EXISTS public.vista_equipos_disponibles;
DROP VIEW IF EXISTS public.vista_equipos_completo;
DROP VIEW IF EXISTS public.vista_accesorios_stock_bajo;
DROP TABLE IF EXISTS public.usuarios;
DROP TABLE IF EXISTS public.unidades_medida;
DROP TABLE IF EXISTS public.tipos_equipos;
DROP TABLE IF EXISTS public.tipos_accesorios;
DROP TABLE IF EXISTS public.proveedores;
DROP TABLE IF EXISTS public.prestamos;
DROP TABLE IF EXISTS public.movimientos_equipos;
DROP TABLE IF EXISTS public.movimientos_accesorios;
DROP TABLE IF EXISTS public.marcas;
DROP TABLE IF EXISTS public.mantenimientos;
DROP TABLE IF EXISTS public.equipos;
DROP TABLE IF EXISTS public.destinos;
DROP TABLE IF EXISTS public.categorias_general;
DROP TABLE IF EXISTS public.categorias;
DROP TABLE IF EXISTS public.app_sessions;
DROP TABLE IF EXISTS public.accesorios;
DROP FUNCTION IF EXISTS public.sync_equipo_estado_from_prestamo_update();
DROP FUNCTION IF EXISTS public.sync_equipo_estado_from_prestamo_insert();
DROP FUNCTION IF EXISTS public.sync_equipo_estado_from_movimiento();
DROP FUNCTION IF EXISTS public.sync_accesorio_stock_from_movimiento();
DROP FUNCTION IF EXISTS public.set_updated_at();
--
-- Name: set_updated_at(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


--
-- Name: sync_accesorio_stock_from_movimiento(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.sync_accesorio_stock_from_movimiento() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.tipo_movimiento = 'SALIDA' THEN
        UPDATE accesorios SET cantidad = GREATEST(cantidad - NEW.cantidad, 0) WHERE id_accesorio = NEW.id_accesorio;
    ELSIF NEW.tipo_movimiento = 'ENTRADA' THEN
        UPDATE accesorios SET cantidad = cantidad + NEW.cantidad WHERE id_accesorio = NEW.id_accesorio;
    END IF;
    RETURN NEW;
END;
$$;


--
-- Name: sync_equipo_estado_from_movimiento(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.sync_equipo_estado_from_movimiento() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.tipo_movimiento = 'SALIDA' THEN
        UPDATE equipos SET estado = 'OCUPADO' WHERE numero_serie_mac = NEW.numero_serie_mac;
    ELSIF NEW.tipo_movimiento = 'ENTRADA' THEN
        UPDATE equipos SET estado = 'DISPONIBLE' WHERE numero_serie_mac = NEW.numero_serie_mac;
    ELSIF NEW.tipo_movimiento = 'MANTENIMIENTO' THEN
        UPDATE equipos SET estado = 'MANTENIMIENTO' WHERE numero_serie_mac = NEW.numero_serie_mac;
    END IF;
    RETURN NEW;
END;
$$;


--
-- Name: sync_equipo_estado_from_prestamo_insert(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.sync_equipo_estado_from_prestamo_insert() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE equipos SET estado = 'OCUPADO' WHERE numero_serie_mac = NEW.numero_serie_mac;
    RETURN NEW;
END;
$$;


--
-- Name: sync_equipo_estado_from_prestamo_update(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.sync_equipo_estado_from_prestamo_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.estado = 'DEVUELTO' AND OLD.estado = 'PRESTADO' THEN
        UPDATE equipos SET estado = 'DISPONIBLE' WHERE numero_serie_mac = NEW.numero_serie_mac;
    END IF;
    RETURN NEW;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: accesorios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.accesorios (
    id_accesorio integer NOT NULL,
    nombre character varying(100) NOT NULL,
    id_categoria integer,
    id_marca integer,
    id_proveedor integer,
    costo numeric(10,2),
    codigo character varying(20),
    cantidad integer DEFAULT 0 NOT NULL,
    unidad_medida character varying(50),
    modelo character varying(100),
    marca character varying(100),
    numero_serie_mac character varying(50),
    fecha_entrada date NOT NULL,
    estado character varying(20) DEFAULT 'DISPONIBLE'::character varying NOT NULL,
    calidad character varying(20) DEFAULT 'BUENO'::character varying NOT NULL,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_accesorios_calidad CHECK (((calidad)::text = ANY ((ARRAY['NUEVO'::character varying, 'BUENO'::character varying, 'REGULAR'::character varying, 'MALO'::character varying])::text[]))),
    CONSTRAINT chk_accesorios_cantidad CHECK ((cantidad >= 0)),
    CONSTRAINT chk_accesorios_estado CHECK (((estado)::text = ANY ((ARRAY['DISPONIBLE'::character varying, 'OCUPADO'::character varying, 'MANTENIMIENTO'::character varying, 'RETIRADO'::character varying])::text[])))
);


--
-- Name: accesorios_id_accesorio_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.accesorios ALTER COLUMN id_accesorio ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.accesorios_id_accesorio_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: app_sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.app_sessions (
    id character varying(128) NOT NULL,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: categorias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categorias (
    id_categoria integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: categorias_general; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categorias_general (
    id_categoria integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    tipo character varying(20) DEFAULT 'EQUIPO'::character varying NOT NULL,
    codigo_base character varying(20),
    activo smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_categorias_general_tipo CHECK (((tipo)::text = ANY ((ARRAY['EQUIPO'::character varying, 'ACCESORIO'::character varying, 'AMBOS'::character varying])::text[])))
);


--
-- Name: categorias_general_id_categoria_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.categorias_general ALTER COLUMN id_categoria ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.categorias_general_id_categoria_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: categorias_id_categoria_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.categorias ALTER COLUMN id_categoria ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.categorias_id_categoria_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: destinos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.destinos (
    id_destino integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: destinos_id_destino_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.destinos ALTER COLUMN id_destino ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.destinos_id_destino_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: equipos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.equipos (
    id_equipo integer NOT NULL,
    nombre character varying(100) NOT NULL,
    id_categoria integer,
    id_marca integer,
    id_proveedor integer,
    costo numeric(10,2),
    numero_serie_mac character varying(50) NOT NULL,
    codigo character varying(20),
    id_tipo_equipo integer,
    modelo character varying(100),
    marca character varying(100),
    fecha_entrada date,
    estado character varying(20) DEFAULT 'DISPONIBLE'::character varying NOT NULL,
    calidad character varying(20) DEFAULT 'BUENO'::character varying NOT NULL,
    observaciones text,
    precio numeric(10,2),
    proveedor character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_equipos_calidad CHECK (((calidad)::text = ANY ((ARRAY['NUEVO'::character varying, 'BUENO'::character varying, 'REGULAR'::character varying, 'MALO'::character varying])::text[]))),
    CONSTRAINT chk_equipos_estado CHECK (((estado)::text = ANY ((ARRAY['DISPONIBLE'::character varying, 'OCUPADO'::character varying, 'MANTENIMIENTO'::character varying, 'RETIRADO'::character varying])::text[])))
);


--
-- Name: equipos_id_equipo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.equipos ALTER COLUMN id_equipo ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.equipos_id_equipo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: mantenimientos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.mantenimientos (
    id_mantenimiento integer NOT NULL,
    id_equipo integer,
    numero_serie_mac character varying(50) NOT NULL,
    tipo_mantenimiento character varying(20) NOT NULL,
    descripcion text NOT NULL,
    tecnico_responsable character varying(100) NOT NULL,
    fecha_ingreso date NOT NULL,
    fecha_entrega date,
    estado character varying(20) DEFAULT 'EN_PROCESO'::character varying NOT NULL,
    costo numeric(10,2),
    repuestos_cambiados text,
    observaciones text,
    garantia_dias integer DEFAULT 0,
    created_by integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_mantenimientos_estado CHECK (((estado)::text = ANY ((ARRAY['EN_PROCESO'::character varying, 'COMPLETADO'::character varying, 'CANCELADO'::character varying])::text[]))),
    CONSTRAINT chk_mantenimientos_tipo CHECK (((tipo_mantenimiento)::text = ANY ((ARRAY['PREVENTIVO'::character varying, 'CORRECTIVO'::character varying, 'EVOLUTIVO'::character varying])::text[])))
);


--
-- Name: mantenimientos_id_mantenimiento_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.mantenimientos ALTER COLUMN id_mantenimiento ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.mantenimientos_id_mantenimiento_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: marcas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.marcas (
    id_marca integer NOT NULL,
    nombre character varying(100) NOT NULL,
    pais_origen character varying(50),
    pagina_web character varying(100),
    activo smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: marcas_id_marca_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.marcas ALTER COLUMN id_marca ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.marcas_id_marca_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: movimientos_accesorios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.movimientos_accesorios (
    id_movimiento integer NOT NULL,
    id_accesorio integer NOT NULL,
    tipo_movimiento character varying(20) NOT NULL,
    cantidad integer NOT NULL,
    asunto character varying(100) NOT NULL,
    encargado character varying(100) NOT NULL,
    destino character varying(100),
    observaciones text,
    observaciones_detalladas text,
    fecha_movimiento date NOT NULL,
    created_by integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_movimientos_accesorios_cantidad CHECK ((cantidad > 0)),
    CONSTRAINT chk_movimientos_accesorios_tipo CHECK (((tipo_movimiento)::text = ANY ((ARRAY['SALIDA'::character varying, 'ENTRADA'::character varying, 'MANTENIMIENTO'::character varying, 'REVISION'::character varying])::text[])))
);


--
-- Name: movimientos_accesorios_id_movimiento_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.movimientos_accesorios ALTER COLUMN id_movimiento ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.movimientos_accesorios_id_movimiento_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: movimientos_equipos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.movimientos_equipos (
    id_movimiento integer NOT NULL,
    id_equipo integer,
    numero_serie_mac character varying(50) NOT NULL,
    tipo_movimiento character varying(20) NOT NULL,
    asunto character varying(100) NOT NULL,
    encargado character varying(100) NOT NULL,
    destino character varying(100),
    observaciones text,
    observaciones_detalladas text,
    fecha_movimiento date NOT NULL,
    created_by integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_movimientos_equipos_tipo CHECK (((tipo_movimiento)::text = ANY ((ARRAY['SALIDA'::character varying, 'ENTRADA'::character varying, 'MANTENIMIENTO'::character varying, 'REVISION'::character varying])::text[])))
);


--
-- Name: movimientos_equipos_id_movimiento_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.movimientos_equipos ALTER COLUMN id_movimiento ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.movimientos_equipos_id_movimiento_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: prestamos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.prestamos (
    id_prestamo integer NOT NULL,
    id_equipo integer,
    numero_serie_mac character varying(50) NOT NULL,
    id_usuario_solicita integer,
    nombre_solicita character varying(100) NOT NULL,
    cargo_solicita character varying(50),
    departamento character varying(100),
    telefono_contacto character varying(20),
    email_contacto character varying(100),
    fecha_prestamo date NOT NULL,
    fecha_devolucion_esperada date NOT NULL,
    fecha_devolucion_real date,
    estado character varying(20) DEFAULT 'PRESTADO'::character varying NOT NULL,
    motivo_prestamo text NOT NULL,
    observaciones_salida text,
    observaciones_devolucion text,
    equipo_entregado_conforme smallint DEFAULT 1 NOT NULL,
    equipo_recibido_conforme smallint DEFAULT 1 NOT NULL,
    id_usuario_entrega integer,
    id_usuario_recibe integer,
    created_by integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_prestamos_estado CHECK (((estado)::text = ANY ((ARRAY['PRESTADO'::character varying, 'DEVUELTO'::character varying, 'ATRASADO'::character varying, 'PERDIDO'::character varying])::text[])))
);


--
-- Name: prestamos_id_prestamo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.prestamos ALTER COLUMN id_prestamo ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.prestamos_id_prestamo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: proveedores; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.proveedores (
    id_proveedor integer NOT NULL,
    nombre_empresa character varying(150) NOT NULL,
    nit character varying(20),
    nombre_contacto character varying(100),
    cargo_contacto character varying(50),
    telefono character varying(20),
    celular character varying(20),
    email character varying(100),
    direccion character varying(200),
    ciudad character varying(50),
    pais character varying(50) DEFAULT 'Bolivia'::character varying,
    pagina_web character varying(100),
    productos text,
    observaciones text,
    activo smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: proveedores_id_proveedor_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.proveedores ALTER COLUMN id_proveedor ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.proveedores_id_proveedor_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tipos_accesorios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tipos_accesorios (
    id_tipo_accesorio integer NOT NULL,
    codigo character varying(40) NOT NULL,
    descripcion character varying(180) NOT NULL,
    categoria character varying(80),
    observaciones text,
    activo smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: tipos_accesorios_id_tipo_accesorio_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.tipos_accesorios ALTER COLUMN id_tipo_accesorio ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.tipos_accesorios_id_tipo_accesorio_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tipos_equipos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tipos_equipos (
    id_codigo integer NOT NULL,
    codigo character varying(20) NOT NULL,
    prefijo character varying(10),
    descripcion character varying(150) NOT NULL,
    categoria character varying(20) DEFAULT 'OTRO'::character varying NOT NULL,
    observaciones text,
    activo smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_tipos_equipos_categoria CHECK (((categoria)::text = ANY ((ARRAY['ROUTER'::character varying, 'ONU'::character varying, 'SWITCH'::character varying, 'OTRO'::character varying])::text[])))
);


--
-- Name: tipos_equipos_id_codigo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.tipos_equipos ALTER COLUMN id_codigo ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.tipos_equipos_id_codigo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: unidades_medida; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unidades_medida (
    id_unidad integer NOT NULL,
    nombre character varying(50) NOT NULL,
    abreviatura character varying(10),
    descripcion text,
    activo smallint DEFAULT 1 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: unidades_medida_id_unidad_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.unidades_medida ALTER COLUMN id_unidad ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.unidades_medida_id_unidad_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuarios (
    id_usuario integer NOT NULL,
    nombre_completo character varying(150) NOT NULL,
    usuario character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    carnet character varying(20) NOT NULL,
    email character varying(100) NOT NULL,
    fecha_nacimiento date NOT NULL,
    rol character varying(20) DEFAULT 'usuario'::character varying NOT NULL,
    activo smallint DEFAULT 1 NOT NULL,
    ultimo_acceso timestamp without time zone,
    reset_token character varying(128),
    reset_token_expires timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_usuarios_rol CHECK (((rol)::text = ANY ((ARRAY['admin'::character varying, 'usuario'::character varying])::text[])))
);


--
-- Name: usuarios_id_usuario_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.usuarios ALTER COLUMN id_usuario ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.usuarios_id_usuario_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: vista_accesorios_stock_bajo; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.vista_accesorios_stock_bajo AS
 SELECT id_accesorio,
    nombre,
    cantidad,
    unidad_medida,
    estado
   FROM public.accesorios
  WHERE (cantidad < 10)
  ORDER BY cantidad;


--
-- Name: vista_equipos_completo; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.vista_equipos_completo AS
 SELECT e.id_equipo,
    e.nombre,
    e.id_categoria,
    e.id_marca,
    e.id_proveedor,
    e.costo,
    e.numero_serie_mac,
    e.codigo,
    e.id_tipo_equipo,
    e.modelo,
    e.marca,
    e.fecha_entrada,
    e.estado,
    e.calidad,
    e.observaciones,
    e.precio,
    e.proveedor,
    e.created_at,
    e.updated_at,
    c.nombre AS categoria_nombre,
    m.nombre AS marca_nombre,
    p.nombre_empresa AS proveedor_nombre
   FROM (((public.equipos e
     LEFT JOIN public.categorias c ON ((e.id_categoria = c.id_categoria)))
     LEFT JOIN public.marcas m ON ((e.id_marca = m.id_marca)))
     LEFT JOIN public.proveedores p ON ((e.id_proveedor = p.id_proveedor)));


--
-- Name: vista_equipos_disponibles; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.vista_equipos_disponibles AS
 SELECT id_equipo,
    nombre,
    codigo,
    numero_serie_mac,
    modelo,
    marca,
    calidad,
    precio,
    fecha_entrada,
    (CURRENT_DATE - fecha_entrada) AS dias_almacenado
   FROM public.equipos
  WHERE ((estado)::text = 'DISPONIBLE'::text)
  ORDER BY nombre, fecha_entrada;


--
-- Name: vista_resumen_equipos_por_codigo; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.vista_resumen_equipos_por_codigo AS
 SELECT e.codigo,
    t.descripcion,
    count(*) AS total,
    sum(
        CASE
            WHEN ((e.estado)::text = 'DISPONIBLE'::text) THEN 1
            ELSE 0
        END) AS disponibles,
    sum(
        CASE
            WHEN ((e.estado)::text = 'OCUPADO'::text) THEN 1
            ELSE 0
        END) AS ocupados,
    sum(
        CASE
            WHEN ((e.estado)::text = 'MANTENIMIENTO'::text) THEN 1
            ELSE 0
        END) AS mantenimiento,
    sum(
        CASE
            WHEN ((e.calidad)::text = 'NUEVO'::text) THEN 1
            ELSE 0
        END) AS nuevos,
    avg(e.precio) AS precio_promedio
   FROM (public.equipos e
     LEFT JOIN public.tipos_equipos t ON (((e.codigo)::text = (t.codigo)::text)))
  GROUP BY e.codigo, t.descripcion
  ORDER BY (count(*)) DESC;


--
-- Name: vista_resumen_movimientos_mes; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.vista_resumen_movimientos_mes AS
 SELECT to_char((fecha_movimiento)::timestamp with time zone, 'YYYY-MM'::text) AS mes,
    tipo_movimiento,
    count(*) AS total_movimientos
   FROM public.movimientos_equipos
  GROUP BY (to_char((fecha_movimiento)::timestamp with time zone, 'YYYY-MM'::text)), tipo_movimiento;


--
-- Data for Name: accesorios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.accesorios (id_accesorio, nombre, id_categoria, id_marca, id_proveedor, costo, codigo, cantidad, unidad_medida, modelo, marca, numero_serie_mac, fecha_entrada, estado, calidad, observaciones, created_at, updated_at) FROM stdin;
1	ROSETAS	\N	\N	\N	\N	\N	75	UNIDADES	-	-	\N	2025-02-06	DISPONIBLE	REGULAR	-	2026-03-31 16:50:08	2026-04-09 04:36:42
2	CABLES UTP	\N	\N	\N	\N	\N	50	METROS	CAT6	INTEX	\N	2025-02-05	DISPONIBLE	NUEVO	\N	2026-03-31 16:50:08	2026-03-31 16:50:08
3	CONECTORES RJ45	\N	\N	\N	\N	\N	200	UNIDADES	-	-	\N	2025-02-04	DISPONIBLE	NUEVO	\N	2026-03-31 16:50:08	2026-03-31 16:50:08
4	FIBRA DROP 1H	\N	\N	\N	\N	FD1H	1000	METROS	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-03-31 16:50:08
5	FIBRA DROP 2H	\N	\N	\N	\N	FD2H	1000	METROS	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-03-31 16:50:08
6	FIBRA DROP 4H	\N	\N	\N	\N	FD4H	1000	METROS	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-03-31 16:50:08
7	FIBRA ADSS	\N	\N	\N	\N	FADD	4000	METROS	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-03-31 16:50:08
9	PATCH CORD SIMPLEX 3 METRO UPC	\N	\N	\N	\N	FPCS3UPC	35	UNIDADES	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-03-31 16:50:08
10	PATCH CORD DUPLEX 3 METRO APC	\N	\N	\N	\N	FPCD3APC	35	UNIDADES	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-03-31 16:50:08
11	PATCH CORD SIMPLEX 3 METRO APC	\N	\N	\N	\N	FPCS3APC	35	UNIDADES	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-03-31 16:50:08
12	PATCH CORD DUPLEX 3 METRO UPC	\N	\N	\N	\N	FPCD3UPC	35	UNIDADES	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-03-31 16:50:08
13	adasd	\N	\N	\N	\N	asdadas	1	METROS	adas			2026-04-08	DISPONIBLE	NUEVO	asdadadasd	2026-04-08 22:02:18	2026-04-08 22:02:18
14	sdadas	\N	\N	\N	\N	sasdas	1	UNIDADES	sdasd	asasd		2026-04-08	DISPONIBLE	NUEVO	adasdasd	2026-04-08 22:02:34	2026-04-08 22:02:34
15	SPLITTER 1 X2	\N	\N	\N	\N	SPL	0	UNIDADES	GENERICO	GENERICO	000000	2026-04-10	DISPONIBLE	NUEVO	SPL-PLC-1X2-SCAPC-001	2026-04-10 16:52:24	2026-04-10 18:07:12
16	SPLITTER OPTIC PLC MINI TUBE S.M.  1*2 CABLE 900um SC/APC	\N	\N	\N	\N	SPL-1X2-SCAPC	2	UNIDADES				2026-04-10	DISPONIBLE	NUEVO		2026-04-10 16:53:58	2026-04-10 16:53:58
17	SPLITTER OPTIC PLC MINI TUBE S.M.  1*2 CABLE 900um SC/APC	\N	\N	\N	\N	SPL-PLC-1X2-SCAPC	1	UNIDADES	SPLITER1X2	KHOMAX	0	2026-04-13	DISPONIBLE	NUEVO		2026-04-13 15:18:54	2026-04-13 15:18:54
18	SPLITER 1X4	\N	\N	\N	\N	SPL-PLC-1X4-SCAPC	10	UNIDADES	0	KHOMAX	0	2026-04-13	DISPONIBLE	NUEVO		2026-04-13 15:26:59	2026-04-13 15:26:59
8	FIBRA ASU	\N	\N	\N	\N	FASU	4015	METROS	-	-	\N	2026-03-23	DISPONIBLE	NUEVO		2026-03-31 16:50:08	2026-04-17 19:36:04.675189
\.


--
-- Data for Name: app_sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.app_sessions (id, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: categorias; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categorias (id_categoria, nombre, descripcion, activo, created_at, updated_at) FROM stdin;
1	Router	Equipos de enrutamiento de red	1	2026-03-31 16:58:41	2026-03-31 16:58:41
2	Switch	Conmutadores de red	1	2026-03-31 16:58:41	2026-03-31 16:58:41
3	ONT	Optical Network Terminal	1	2026-03-31 16:58:41	2026-03-31 16:58:41
4	Cable	Cables de red y fibra optica	1	2026-03-31 16:58:41	2026-03-31 16:58:41
5	Conector	Conectores y adaptadores	1	2026-03-31 16:58:41	2026-03-31 16:58:41
6	Accesorio	Accesorios diversos de red	1	2026-03-31 16:58:41	2026-03-31 16:58:41
\.


--
-- Data for Name: categorias_general; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categorias_general (id_categoria, nombre, descripcion, tipo, codigo_base, activo, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: destinos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.destinos (id_destino, nombre, descripcion, activo, created_at) FROM stdin;
1	INSTALACIONES	Equipos para instalaciones de red	1	2026-03-31 16:50:08
2	INFRAESTRUCTURA	Equipos para infraestructura	1	2026-03-31 16:50:08
3	MANTENIMIENTO VEHICULOS	Equipos para mantenimiento de vehiculos	1	2026-03-31 16:50:08
4	MANTENIMIENTO RED	Equipos para mantenimiento de red	1	2026-03-31 16:50:08
5	ADMINISTRACION	Equipos administrativos	1	2026-03-31 16:50:08
6	SOPORTE TECNICO	Equipos para soporte tecnico	1	2026-03-31 16:50:08
\.


--
-- Data for Name: equipos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.equipos (id_equipo, nombre, id_categoria, id_marca, id_proveedor, costo, numero_serie_mac, codigo, id_tipo_equipo, modelo, marca, fecha_entrada, estado, calidad, observaciones, precio, proveedor, created_at, updated_at) FROM stdin;
2	ONT	\N	\N	\N	\N	ONT123456789	ONU2.4	\N	FiberHome	AN5506	2025-02-05	OCUPADO	NUEVO	\N	290.00	DIGICORP	2026-03-31 16:50:08	2026-04-08 23:19:56
3	SWITCH	\N	\N	\N	\N	SW987654321	\N	\N	SG108	TP-Link	2025-02-04	OCUPADO	BUENO	En uso	150.00	DIGICORP	2026-03-31 16:50:08	2026-04-02 15:55:28
5	ONU ROUTER	\N	\N	\N	\N	123	ONUR	5			2026-04-02	DISPONIBLE	NUEVO		\N		2026-04-02 16:13:17	2026-04-02 16:13:17
6	ONU WIFI 2.4-5G	\N	\N	\N	\N	GPON0073D72E	ONUDUAL	4	V2802DAC	V-SOL	2026-04-02	DISPONIBLE	NUEVO		369.00	DIGICORP	2026-04-02 16:16:28	2026-04-02 16:16:28
7	patito	\N	\N	\N	\N	2344234234	ONU2.4	3	P	PATITO	2026-04-08	DISPONIBLE	NUEVO	es un error esto no existe	234.00	comadre	2026-04-08 14:47:32	2026-04-08 14:47:32
10	ROUTER DUAL BAND 2.4-5G	\N	\N	\N	\N	GPON0073DF4E	ROT52.4	2	V2802DAC	V-SOL	2026-04-10	OCUPADO	NUEVO		369.00	DIGICORP	2026-04-10 16:57:38	2026-04-10 18:05:47
11	ONU MODO DUAL 1 XPON+2GE+WIFI5	\N	\N	\N	\N	GPON0073D15E	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 14:43:48	2026-04-13 14:43:48
12	ONU WIFI 2.4-5G	\N	\N	\N	\N	GPON0073DD4E	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 14:44:56	2026-04-13 14:44:56
13	ONU MODO DUAL 1 XPON+2GE+WIFI5	\N	\N	\N	\N	GPON0073D98E	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 14:46:53	2026-04-13 14:46:53
14	ONU MODO DUAL 1 XPON+2GE+WIFI5	\N	\N	\N	\N	GPON0073DDDE	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 14:51:09	2026-04-13 14:51:09
15	ONU MODO DUAL 1 XPON+2GE+WIFI5	\N	\N	\N	\N	GPON0073D94E	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 14:54:48	2026-04-13 14:54:48
16	ONU MODO DUAL 1 XPON+2GE+WIFI5	\N	\N	\N	\N	GPON0073D39E	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 14:55:49	2026-04-13 14:55:49
17	ONU MODO DUAL 1 XPON+2GE+WIFI5	\N	\N	\N	\N	GPON0073D57E	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 15:00:13	2026-04-13 15:00:13
18	ONU MODO DUAL 1 XPON+2GE+WIFI5	\N	\N	\N	\N	GPON0073DAEE	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 15:01:20	2026-04-13 15:01:20
19	ONU MODO DUAL 1 XPON+2GE+WIFI5	\N	\N	\N	\N	GPON0073DCAE	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 15:02:34	2026-04-13 15:02:34
20	ONU WIFI 2.4-5G	\N	\N	\N	\N	GPON0073DBAE	ONUDUAL	4	V2802DAC	VSOL	2026-04-13	DISPONIBLE	NUEVO		390.00	DIGICORP	2026-04-13 15:03:16	2026-04-13 15:03:16
1	ROUTER	\N	\N	\N	\N	445121884JXX	ROT2.4	\N	PON	XPON	2025-02-06	OCUPADO	BUENO	Equipo de prueba	290.00	DIGICORP	2026-03-31 16:50:08	2026-04-17 19:37:47.846346
8	ONU WIFI 2.4-5G	\N	\N	\N	\N	GPON0073E2BE	ONUDUAL	4	V2802DAC	V-SOL	2026-04-08	OCUPADO	NUEVO		369.00	DIGICORP	2026-04-08 16:14:36	2026-04-20 14:50:50.166305
\.


--
-- Data for Name: mantenimientos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.mantenimientos (id_mantenimiento, id_equipo, numero_serie_mac, tipo_mantenimiento, descripcion, tecnico_responsable, fecha_ingreso, fecha_entrega, estado, costo, repuestos_cambiados, observaciones, garantia_dias, created_by, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: marcas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.marcas (id_marca, nombre, pais_origen, pagina_web, activo, created_at, updated_at) FROM stdin;
1	TP-Link	China	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
2	Huawei	China	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
3	ZTE	China	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
4	Cisco	Estados Unidos	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
5	Nokia	Finlandia	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
6	FiberHome	China	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
7	XPON	China	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
8	Generico	Varios	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
\.


--
-- Data for Name: movimientos_accesorios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.movimientos_accesorios (id_movimiento, id_accesorio, tipo_movimiento, cantidad, asunto, encargado, destino, observaciones, observaciones_detalladas, fecha_movimiento, created_by, created_at) FROM stdin;
1	1	SALIDA	10	INSTALACION	OTROS	\N			2026-04-09	1	2026-04-09 04:36:42
2	15	SALIDA	1	INSTALACION	EQUIPO 1	MATILDE			2026-04-10	1	2026-04-10 18:07:12
\.


--
-- Data for Name: movimientos_equipos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.movimientos_equipos (id_movimiento, id_equipo, numero_serie_mac, tipo_movimiento, asunto, encargado, destino, observaciones, observaciones_detalladas, fecha_movimiento, created_by, created_at) FROM stdin;
1	2	ONT123456789	SALIDA	INSTALACION	xdfsd	MANTENIMIENTO RED	sfsdf	sfsdf	2026-04-08	1	2026-04-08 23:19:56
2	10	GPON0073DF4E	SALIDA	INSTALACION	Rey	INSTALACIONES			2026-04-10	1	2026-04-10 18:05:47
3	10	GPON0073DF4E	SALIDA	INSTALACION	Rey	SOPORTE TECNICO			2026-04-13	1	2026-04-13 18:38:58
4	\N	445121884JXX	SALIDA	INSTALACIÓN	Carlos	INSTALACIONES			2026-04-17	1	2026-04-17 19:37:47.846346
5	\N	GPON0073E2BE	SALIDA	INSTALACIÓN	CARLOS	INSTALACIONES	Héctor Villca Calle		2026-04-20	1	2026-04-20 14:50:50.166305
\.


--
-- Data for Name: prestamos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.prestamos (id_prestamo, id_equipo, numero_serie_mac, id_usuario_solicita, nombre_solicita, cargo_solicita, departamento, telefono_contacto, email_contacto, fecha_prestamo, fecha_devolucion_esperada, fecha_devolucion_real, estado, motivo_prestamo, observaciones_salida, observaciones_devolucion, equipo_entregado_conforme, equipo_recibido_conforme, id_usuario_entrega, id_usuario_recibe, created_by, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: proveedores; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.proveedores (id_proveedor, nombre_empresa, nit, nombre_contacto, cargo_contacto, telefono, celular, email, direccion, ciudad, pais, pagina_web, productos, observaciones, activo, created_at, updated_at) FROM stdin;
1	Importadora de Equipos de Red S.R.L.	1234567890	Juan Perez	Gerente de Ventas	2222222	\N	ventas@importadorared.bo	Av. Banzer Km 5.5	Santa Cruz	Bolivia	\N	Routers, Switches, ONTs	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
2	TecnoComunicaciones S.A.	9876543210	Maria Garcia	Ejecutiva de Cuenta	3333333	\N	maria.garcia@tecnocom.bo	Calle Loayza 456	La Paz	Bolivia	\N	Cables, Conectores, Accesorios	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
3	Distribuidora Nacional de Tecnologia	5555555555	Carlos Lopez	Supervisor	4444444	\N	carlos@distritec.bo	Av. America 789	Cochabamba	Bolivia	\N	Equipos de red variados	\N	1	2026-03-31 16:58:41	2026-03-31 16:58:41
\.


--
-- Data for Name: tipos_accesorios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tipos_accesorios (id_tipo_accesorio, codigo, descripcion, categoria, observaciones, activo, created_at, updated_at) FROM stdin;
1	FASU	FIBRA ASU	METROS		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
2	FPCS3APC	PATCH CORD SIMPLEX 3 METRO APC	UNIDADES		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
3	FPCS3UPC	PATCH CORD SIMPLEX 3 METRO UPC	UNIDADES		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
4	sasdas	sdadas	UNIDADES	adasdasd	1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
5	SPL	SPLITTER 1 X2	UNIDADES	SPL-PLC-1X2-SCAPC-001	1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
6	FD1H	FIBRA DROP 1H	METROS		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
7	SPL-1X2-SCAPC	SPLITTER OPTIC PLC MINI TUBE S.M.  1*2 CABLE 900um SC/APC	UNIDADES		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
8	FPCD3UPC	PATCH CORD DUPLEX 3 METRO UPC	UNIDADES		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
9	FPCD3APC	PATCH CORD DUPLEX 3 METRO APC	UNIDADES		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
10	SPL-PLC-1X2-SCAPC	SPLITTER OPTIC PLC MINI TUBE S.M.  1*2 CABLE 900um SC/APC	UNIDADES		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
11	SPL-PLC-1X4-SCAPC	SPLITER 1X4	UNIDADES		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
12	FD4H	FIBRA DROP 4H	METROS		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
13	FD2H	FIBRA DROP 2H	METROS		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
14	asdadas	adasd	METROS	asdadadasd	1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
15	FADD	FIBRA ADSS	METROS		1	2026-04-17 04:52:31.917887	2026-04-17 04:52:31.917887
\.


--
-- Data for Name: tipos_equipos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tipos_equipos (id_codigo, codigo, prefijo, descripcion, categoria, observaciones, activo, created_at, updated_at) FROM stdin;
1	ROT2.4	ROT	ROUTER WIFI 2.4	ROUTER	\N	1	2026-03-31 16:50:08	2026-03-31 20:14:35
2	ROT52.4	ROT	ROUTER DUAL BAND 2.4-5G	ROUTER	\N	1	2026-03-31 16:50:08	2026-03-31 20:14:35
3	ONU2.4	ONU	ONU WIFI 2.4	ONU	\N	1	2026-03-31 16:50:08	2026-03-31 20:14:35
4	ONUDUAL	ONU	ONU WIFI 2.4-5G	ONU	\N	1	2026-03-31 16:50:08	2026-03-31 20:14:35
5	ONUR	ONU	ONU ROUTER	ONU	\N	1	2026-03-31 16:50:08	2026-03-31 20:14:35
6	ONUB	ONU	ONUBRIDGED	ONU	\N	1	2026-03-31 16:50:08	2026-03-31 20:14:35
7	SASD	SAS	adasd	SWITCH	adasdsa	1	2026-04-08 17:31:54	2026-04-08 21:31:54
8	QWEW	QWE	asdas	ROUTER	testsetes	1	2026-04-09 16:14:09	2026-04-09 20:14:09
9	SDFD	SDF	xggdf	SWITCH	yaraararara	1	2026-04-09 16:15:15	2026-04-09 20:15:15
10	SDFSDF	ASASD	asdasfa	ROUTER	sfsdfsdfsdgsd	1	2026-04-09 16:15:46	2026-04-09 20:15:46
11	ROUTER PRUEBA	ROU	ruter de prueba	ROUTER	es solo para prueba	1	2026-04-09 16:18:46	2026-04-09 20:18:46
12	EQ-RB	EQ	Router potente con 10 puertos Gigabit	OTRO	enrutador	1	2026-04-10 09:36:05	2026-04-10 13:36:05
13	SPL-PLC-1X2-SCAPC	SPL	SPLITTER OPTIC PLC MINI TUBE S.M.  1*2 CABLE 900um SC/APC	OTRO	MATERIAL	1	2026-04-13 11:17:09	2026-04-13 15:17:09
\.


--
-- Data for Name: unidades_medida; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unidades_medida (id_unidad, nombre, abreviatura, descripcion, activo, created_at) FROM stdin;
1	METROS	m	Metro lineal	1	2026-03-31 16:50:08
2	UNIDADES	ud	Unidad individual	1	2026-03-31 16:50:08
3	CAJAS	caja	Caja contenedora	1	2026-03-31 16:50:08
4	KILOS	kg	Kilogramo	1	2026-03-31 16:50:08
5	LITROS	l	Litro	1	2026-03-31 16:50:08
6	PARES	par	Par de elementos	1	2026-03-31 16:50:08
\.


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.usuarios (id_usuario, nombre_completo, usuario, password_hash, carnet, email, fecha_nacimiento, rol, activo, ultimo_acceso, reset_token, reset_token_expires, created_at, updated_at) FROM stdin;
2	Raul Choque Paco	rchoque	$2y$10$1N.ro3SFTHup5C0/LfBOeOUtgB7dpOgf3j9dfLrm774R1TlQ1Ixeu	7003229	rchoque@toi.bo	1985-08-02	usuario	1	2026-04-01 21:40:42	\N	\N	2026-03-31 22:42:02	2026-04-01 21:40:42
1	Administrador Principal TOI	admin	$2y$10$vUQVRy7l6a5Owxf0swlZyelDj3pnJM2iT/B2Q9ckt.38MzvurAllC	00000000	admin@toi.bo	1990-01-01	admin	1	2026-04-21 21:25:15.830318	\N	\N	2026-03-31 16:50:07	2026-04-21 21:25:15.830318
\.


--
-- Name: accesorios_id_accesorio_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.accesorios_id_accesorio_seq', 19, true);


--
-- Name: categorias_general_id_categoria_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categorias_general_id_categoria_seq', 1, true);


--
-- Name: categorias_id_categoria_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categorias_id_categoria_seq', 6, true);


--
-- Name: destinos_id_destino_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.destinos_id_destino_seq', 6, true);


--
-- Name: equipos_id_equipo_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.equipos_id_equipo_seq', 22, true);


--
-- Name: mantenimientos_id_mantenimiento_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.mantenimientos_id_mantenimiento_seq', 1, true);


--
-- Name: marcas_id_marca_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.marcas_id_marca_seq', 8, true);


--
-- Name: movimientos_accesorios_id_movimiento_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.movimientos_accesorios_id_movimiento_seq', 2, true);


--
-- Name: movimientos_equipos_id_movimiento_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.movimientos_equipos_id_movimiento_seq', 5, true);


--
-- Name: prestamos_id_prestamo_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.prestamos_id_prestamo_seq', 1, true);


--
-- Name: proveedores_id_proveedor_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.proveedores_id_proveedor_seq', 3, true);


--
-- Name: tipos_accesorios_id_tipo_accesorio_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tipos_accesorios_id_tipo_accesorio_seq', 255, true);


--
-- Name: tipos_equipos_id_codigo_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tipos_equipos_id_codigo_seq', 13, true);


--
-- Name: unidades_medida_id_unidad_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unidades_medida_id_unidad_seq', 6, true);


--
-- Name: usuarios_id_usuario_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.usuarios_id_usuario_seq', 3, true);


--
-- Name: accesorios accesorios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.accesorios
    ADD CONSTRAINT accesorios_pkey PRIMARY KEY (id_accesorio);


--
-- Name: app_sessions app_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_sessions
    ADD CONSTRAINT app_sessions_pkey PRIMARY KEY (id);


--
-- Name: categorias_general categorias_general_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias_general
    ADD CONSTRAINT categorias_general_pkey PRIMARY KEY (id_categoria);


--
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id_categoria);


--
-- Name: destinos destinos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.destinos
    ADD CONSTRAINT destinos_pkey PRIMARY KEY (id_destino);


--
-- Name: equipos equipos_numero_serie_mac_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos
    ADD CONSTRAINT equipos_numero_serie_mac_key UNIQUE (numero_serie_mac);


--
-- Name: equipos equipos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos
    ADD CONSTRAINT equipos_pkey PRIMARY KEY (id_equipo);


--
-- Name: mantenimientos mantenimientos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mantenimientos
    ADD CONSTRAINT mantenimientos_pkey PRIMARY KEY (id_mantenimiento);


--
-- Name: marcas marcas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.marcas
    ADD CONSTRAINT marcas_pkey PRIMARY KEY (id_marca);


--
-- Name: movimientos_accesorios movimientos_accesorios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_accesorios
    ADD CONSTRAINT movimientos_accesorios_pkey PRIMARY KEY (id_movimiento);


--
-- Name: movimientos_equipos movimientos_equipos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_equipos
    ADD CONSTRAINT movimientos_equipos_pkey PRIMARY KEY (id_movimiento);


--
-- Name: prestamos prestamos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestamos
    ADD CONSTRAINT prestamos_pkey PRIMARY KEY (id_prestamo);


--
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id_proveedor);


--
-- Name: tipos_accesorios tipos_accesorios_codigo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_accesorios
    ADD CONSTRAINT tipos_accesorios_codigo_key UNIQUE (codigo);


--
-- Name: tipos_accesorios tipos_accesorios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_accesorios
    ADD CONSTRAINT tipos_accesorios_pkey PRIMARY KEY (id_tipo_accesorio);


--
-- Name: tipos_equipos tipos_equipos_codigo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_equipos
    ADD CONSTRAINT tipos_equipos_codigo_key UNIQUE (codigo);


--
-- Name: tipos_equipos tipos_equipos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipos_equipos
    ADD CONSTRAINT tipos_equipos_pkey PRIMARY KEY (id_codigo);


--
-- Name: unidades_medida unidades_medida_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades_medida
    ADD CONSTRAINT unidades_medida_pkey PRIMARY KEY (id_unidad);


--
-- Name: usuarios usuarios_carnet_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_carnet_key UNIQUE (carnet);


--
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id_usuario);


--
-- Name: usuarios usuarios_usuario_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_usuario_key UNIQUE (usuario);


--
-- Name: idx_accesorios_codigo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_accesorios_codigo ON public.accesorios USING btree (codigo);


--
-- Name: idx_app_sessions_last_activity; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_app_sessions_last_activity ON public.app_sessions USING btree (last_activity);


--
-- Name: idx_equipos_codigo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_equipos_codigo ON public.equipos USING btree (codigo);


--
-- Name: idx_equipos_estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_equipos_estado ON public.equipos USING btree (estado);


--
-- Name: idx_movimientos_accesorios_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_movimientos_accesorios_fecha ON public.movimientos_accesorios USING btree (fecha_movimiento);


--
-- Name: idx_movimientos_equipos_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_movimientos_equipos_fecha ON public.movimientos_equipos USING btree (fecha_movimiento);


--
-- Name: accesorios trg_accesorios_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_accesorios_updated_at BEFORE UPDATE ON public.accesorios FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: categorias_general trg_categorias_general_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_categorias_general_updated_at BEFORE UPDATE ON public.categorias_general FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: categorias trg_categorias_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_categorias_updated_at BEFORE UPDATE ON public.categorias FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: equipos trg_equipos_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_equipos_updated_at BEFORE UPDATE ON public.equipos FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: mantenimientos trg_mantenimientos_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_mantenimientos_updated_at BEFORE UPDATE ON public.mantenimientos FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: marcas trg_marcas_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_marcas_updated_at BEFORE UPDATE ON public.marcas FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: movimientos_accesorios trg_movimiento_accesorio_insert; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_movimiento_accesorio_insert AFTER INSERT ON public.movimientos_accesorios FOR EACH ROW EXECUTE FUNCTION public.sync_accesorio_stock_from_movimiento();


--
-- Name: movimientos_equipos trg_movimiento_equipo_insert; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_movimiento_equipo_insert AFTER INSERT ON public.movimientos_equipos FOR EACH ROW EXECUTE FUNCTION public.sync_equipo_estado_from_movimiento();


--
-- Name: prestamos trg_prestamo_insert; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_prestamo_insert AFTER INSERT ON public.prestamos FOR EACH ROW EXECUTE FUNCTION public.sync_equipo_estado_from_prestamo_insert();


--
-- Name: prestamos trg_prestamo_update; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_prestamo_update AFTER UPDATE ON public.prestamos FOR EACH ROW EXECUTE FUNCTION public.sync_equipo_estado_from_prestamo_update();


--
-- Name: prestamos trg_prestamos_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_prestamos_updated_at BEFORE UPDATE ON public.prestamos FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: proveedores trg_proveedores_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_proveedores_updated_at BEFORE UPDATE ON public.proveedores FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: tipos_equipos trg_tipos_equipos_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_tipos_equipos_updated_at BEFORE UPDATE ON public.tipos_equipos FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: usuarios trg_usuarios_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_usuarios_updated_at BEFORE UPDATE ON public.usuarios FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: accesorios accesorios_id_categoria_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.accesorios
    ADD CONSTRAINT accesorios_id_categoria_fkey FOREIGN KEY (id_categoria) REFERENCES public.categorias(id_categoria) ON DELETE SET NULL;


--
-- Name: accesorios accesorios_id_marca_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.accesorios
    ADD CONSTRAINT accesorios_id_marca_fkey FOREIGN KEY (id_marca) REFERENCES public.marcas(id_marca) ON DELETE SET NULL;


--
-- Name: accesorios accesorios_id_proveedor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.accesorios
    ADD CONSTRAINT accesorios_id_proveedor_fkey FOREIGN KEY (id_proveedor) REFERENCES public.proveedores(id_proveedor) ON DELETE SET NULL;


--
-- Name: equipos equipos_codigo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos
    ADD CONSTRAINT equipos_codigo_fkey FOREIGN KEY (codigo) REFERENCES public.tipos_equipos(codigo) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: equipos equipos_id_categoria_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos
    ADD CONSTRAINT equipos_id_categoria_fkey FOREIGN KEY (id_categoria) REFERENCES public.categorias(id_categoria) ON DELETE SET NULL;


--
-- Name: equipos equipos_id_marca_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos
    ADD CONSTRAINT equipos_id_marca_fkey FOREIGN KEY (id_marca) REFERENCES public.marcas(id_marca) ON DELETE SET NULL;


--
-- Name: equipos equipos_id_proveedor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos
    ADD CONSTRAINT equipos_id_proveedor_fkey FOREIGN KEY (id_proveedor) REFERENCES public.proveedores(id_proveedor) ON DELETE SET NULL;


--
-- Name: equipos equipos_id_tipo_equipo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.equipos
    ADD CONSTRAINT equipos_id_tipo_equipo_fkey FOREIGN KEY (id_tipo_equipo) REFERENCES public.tipos_equipos(id_codigo) ON DELETE SET NULL;


--
-- Name: mantenimientos mantenimientos_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mantenimientos
    ADD CONSTRAINT mantenimientos_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;


--
-- Name: mantenimientos mantenimientos_id_equipo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mantenimientos
    ADD CONSTRAINT mantenimientos_id_equipo_fkey FOREIGN KEY (id_equipo) REFERENCES public.equipos(id_equipo) ON DELETE SET NULL;


--
-- Name: mantenimientos mantenimientos_numero_serie_mac_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mantenimientos
    ADD CONSTRAINT mantenimientos_numero_serie_mac_fkey FOREIGN KEY (numero_serie_mac) REFERENCES public.equipos(numero_serie_mac) ON UPDATE CASCADE;


--
-- Name: movimientos_accesorios movimientos_accesorios_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_accesorios
    ADD CONSTRAINT movimientos_accesorios_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;


--
-- Name: movimientos_accesorios movimientos_accesorios_id_accesorio_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_accesorios
    ADD CONSTRAINT movimientos_accesorios_id_accesorio_fkey FOREIGN KEY (id_accesorio) REFERENCES public.accesorios(id_accesorio) ON DELETE RESTRICT;


--
-- Name: movimientos_equipos movimientos_equipos_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_equipos
    ADD CONSTRAINT movimientos_equipos_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;


--
-- Name: movimientos_equipos movimientos_equipos_id_equipo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_equipos
    ADD CONSTRAINT movimientos_equipos_id_equipo_fkey FOREIGN KEY (id_equipo) REFERENCES public.equipos(id_equipo) ON DELETE SET NULL;


--
-- Name: movimientos_equipos movimientos_equipos_numero_serie_mac_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_equipos
    ADD CONSTRAINT movimientos_equipos_numero_serie_mac_fkey FOREIGN KEY (numero_serie_mac) REFERENCES public.equipos(numero_serie_mac) ON UPDATE CASCADE;


--
-- Name: prestamos prestamos_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestamos
    ADD CONSTRAINT prestamos_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;


--
-- Name: prestamos prestamos_id_equipo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestamos
    ADD CONSTRAINT prestamos_id_equipo_fkey FOREIGN KEY (id_equipo) REFERENCES public.equipos(id_equipo) ON DELETE SET NULL;


--
-- Name: prestamos prestamos_id_usuario_entrega_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestamos
    ADD CONSTRAINT prestamos_id_usuario_entrega_fkey FOREIGN KEY (id_usuario_entrega) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;


--
-- Name: prestamos prestamos_id_usuario_recibe_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestamos
    ADD CONSTRAINT prestamos_id_usuario_recibe_fkey FOREIGN KEY (id_usuario_recibe) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;


--
-- Name: prestamos prestamos_id_usuario_solicita_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestamos
    ADD CONSTRAINT prestamos_id_usuario_solicita_fkey FOREIGN KEY (id_usuario_solicita) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;


--
-- Name: prestamos prestamos_numero_serie_mac_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.prestamos
    ADD CONSTRAINT prestamos_numero_serie_mac_fkey FOREIGN KEY (numero_serie_mac) REFERENCES public.equipos(numero_serie_mac) ON UPDATE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict CF0Qj6dIqWiIp4eAFltPC0ZvZYm2pqIAYPauI4spI4UzpeJgBLc7cWcoJW3cenR

