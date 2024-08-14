import { TestBed } from '@angular/core/testing';

import { SugestoesService } from './sugestoes.service';

describe('SugestoesService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: SugestoesService = TestBed.get(SugestoesService);
    expect(service).toBeTruthy();
  });
});
