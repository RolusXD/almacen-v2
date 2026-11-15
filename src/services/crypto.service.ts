import crypto from 'crypto';
import { ENV } from '../config/env';

const ALGORITHM = 'aes-256-gcm';
const IV_LENGTH = 12; // Recomendado para GCM

function getEncryptionKey(): Buffer {
  const raw = ENV.ENCRYPTION_KEY || '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
  if (raw.length === 64) {
    return Buffer.from(raw, 'hex');
  }
  return crypto.createHash('sha256').update(raw).digest();
}

export class CryptoService {
  /**
   * Cifra un texto utilizando AES-256-GCM con autenticación integrada
   */
  static encrypt(plainText: string): string {
    if (!plainText) return '';
    const key = getEncryptionKey();
    const iv = crypto.randomBytes(IV_LENGTH);
    const cipher = crypto.createCipheriv(ALGORITHM, key, iv);
    
    let encrypted = cipher.update(plainText, 'utf8', 'hex');
    encrypted += cipher.final('hex');
    const authTag = cipher.getAuthTag().toString('hex');
    
    // Retorna iv:authTag:ciphertext
    return `${iv.toString('hex')}:${authTag}:${encrypted}`;
  }

  /**
   * Descifra un texto cifrado en formato iv:authTag:ciphertext
   */
  static decrypt(cipherPayload: string): string {
    if (!cipherPayload) return '';
    try {
      const parts = cipherPayload.split(':');
      if (parts.length !== 3) {
        throw new Error('Formato de carga cifrada inválido');
      }

      const [ivHex, tagHex, encryptedHex] = parts;
      const key = getEncryptionKey();
      const iv = Buffer.from(ivHex, 'hex');
      const authTag = Buffer.from(tagHex, 'hex');

      const decipher = crypto.createDecipheriv(ALGORITHM, key, iv);
      decipher.setAuthTag(authTag);

      let decrypted = decipher.update(encryptedHex, 'hex', 'utf8');
      decrypted += decipher.final('utf8');
      return decrypted;
    } catch (err: any) {
      console.error('Error al descifrar dato protegido:', err.message);
      return '';
    }
  }
}
