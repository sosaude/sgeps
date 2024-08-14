import { TestBed } from '@angular/core/testing';

import { PlanoSaudeService } from './plano-saude.service';

describe('PlanoSaudeService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: PlanoSaudeService = TestBed.get(PlanoSaudeService);
    expect(service).toBeTruthy();
  });
});
