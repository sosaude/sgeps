import { TestBed } from '@angular/core/testing';

import { ClinicasService } from './clinicas.service';

describe('ClinicasService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ClinicasService = TestBed.get(ClinicasService);
    expect(service).toBeTruthy();
  });
});
