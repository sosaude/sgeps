import { Role } from './role';
export class User {
    active: boolean;
    codigo_login: number;
    agencia_id: number;
    deleted_at: string;
    email: string;
    id: string;
    loged_once: boolean;
    login_attempts: Object;
    nome: string;
    role: Role;
    utilizador_administracao_id?: number;
    utilizador_clinica_id?: number;
    utilizador_empresa_id?: number;
    utilizador_farmacia_id?: number;
}