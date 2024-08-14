import { TestBed } from '@angular/core/testing';

import { DependentesService } from './dependentes.service';

describe('DependentesService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: DependentesService = TestBed.get(DependentesService);
    expect(service).toBeTruthy();
  });
});
