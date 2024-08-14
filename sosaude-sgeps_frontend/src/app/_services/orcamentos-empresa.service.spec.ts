import { TestBed } from '@angular/core/testing';

import { OrcamentosEmpresaService } from './orcamentos-empresa.service';

describe('OrcamentosEmpresaService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: OrcamentosEmpresaService = TestBed.get(OrcamentosEmpresaService);
    expect(service).toBeTruthy();
  });
});
