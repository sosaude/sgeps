import { TestBed } from '@angular/core/testing';

import { FarmaceuticosService } from './farmaceuticos.service';

describe('FarmaceuticosService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: FarmaceuticosService = TestBed.get(FarmaceuticosService);
    expect(service).toBeTruthy();
  });
});
