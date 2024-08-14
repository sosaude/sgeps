import { TestBed } from '@angular/core/testing';

import { FarmaciasService } from './farmacias.service';

describe('FarmaciasService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: FarmaciasService = TestBed.get(FarmaciasService);
    expect(service).toBeTruthy();
  });
});
