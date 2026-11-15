import bcrypt from 'bcryptjs';
import { query, queryOne } from '../db';
import { Usuario } from '../models/types';

export class UsuarioRepository {
  async findByUsuario(usuario: string): Promise<Usuario | null> {
    return queryOne<Usuario>(
      'SELECT * FROM usuarios WHERE usuario = $1 AND activo = 1',
      [usuario.trim()]
    );
  }

  async findById(id: number): Promise<Usuario | null> {
    return queryOne<Usuario>(
      'SELECT * FROM usuarios WHERE id_usuario = $1',
      [id]
    );
  }

  async verifyCredentials(usuario: string, pass: string): Promise<Usuario | null> {
    const user = await this.findByUsuario(usuario);
    if (!user) return null;

    // Compatible con hashes bcrypt de PHP ($2y$ o $2b$ o $2a$)
    const normalizedHash = user.password_hash.replace(/^\$2y\$/, '$2a$');
    const isValid = await bcrypt.compare(pass, normalizedHash);
    if (!isValid) return null;

    // Actualizar último acceso
    await query(
      'UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id_usuario = $1',
      [user.id_usuario]
    );

    return user;
  }

  async getAll(): Promise<Usuario[]> {
    return query<Usuario>(
      'SELECT id_usuario, nombre_completo, usuario, carnet, email, fecha_nacimiento, rol, activo, ultimo_acceso, created_at, updated_at FROM usuarios ORDER BY nombre_completo ASC'
    );
  }

  async create(data: {
    nombre_completo: string;
    usuario: string;
    password_hash: string;
    carnet: string;
    email: string;
    fecha_nacimiento?: string;
    rol: 'admin' | 'usuario';
  }): Promise<Usuario> {
    const fechaNac = data.fecha_nacimiento && data.fecha_nacimiento.trim() !== ''
      ? data.fecha_nacimiento.trim()
      : '1995-01-01';

    const res = await query<Usuario>(
      `INSERT INTO usuarios (nombre_completo, usuario, password_hash, carnet, email, fecha_nacimiento, rol, activo)
       VALUES ($1, $2, $3, $4, $5, $6, $7, 1)
       RETURNING *`,
      [
        data.nombre_completo,
        data.usuario,
        data.password_hash,
        data.carnet,
        data.email,
        fechaNac,
        data.rol,
      ]
    );
    return res[0];
  }

  async update(id: number, data: Partial<Usuario>): Promise<void> {
    const fields: string[] = [];
    const values: any[] = [];
    let idx = 1;

    for (const [key, val] of Object.entries(data)) {
      if (['nombre_completo', 'carnet', 'email', 'fecha_nacimiento', 'rol', 'activo', 'password_hash'].includes(key)) {
        fields.push(`${key} = $${idx++}`);
        values.push(val);
      }
    }

    if (fields.length === 0) return;
    values.push(id);
    await query(`UPDATE usuarios SET ${fields.join(', ')} WHERE id_usuario = $${idx}`, values);
  }
}
