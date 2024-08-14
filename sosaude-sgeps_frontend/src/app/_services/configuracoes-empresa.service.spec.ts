import { TestBed } from '@angular/core/testing';

import { ConfiguracoesEmpresaService } from './configuracoes-empresa.service';

describe('ConfiguracoesEmpresaService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ConfiguracoesEmpresaService = TestBed.get(ConfiguracoesEmpresaService);
    expect(service).toBeTruthy();
  });
});
