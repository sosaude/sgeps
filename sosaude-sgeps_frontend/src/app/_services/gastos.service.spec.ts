import { TestBed } from '@angular/core/testing';

import { GastosService } from './gastos.service';

describe('GastosService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: GastosService = TestBed.get(GastosService);
    expect(service).toBeTruthy();
  });
});
