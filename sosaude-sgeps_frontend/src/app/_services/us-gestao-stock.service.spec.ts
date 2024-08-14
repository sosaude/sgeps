import { TestBed } from '@angular/core/testing';

import { UsGestaoStockService } from './us-gestao-stock.service';

describe('UsGestaoStockService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: UsGestaoStockService = TestBed.get(UsGestaoStockService);
    expect(service).toBeTruthy();
  });
});
