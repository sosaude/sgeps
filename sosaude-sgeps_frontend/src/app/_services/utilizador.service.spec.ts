import { TestBed } from '@angular/core/testing';

import { UtilizadorService } from './utilizador.service';

describe('UtilizadorService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: UtilizadorService = TestBed.get(UtilizadorService);
    expect(service).toBeTruthy();
  });
});
