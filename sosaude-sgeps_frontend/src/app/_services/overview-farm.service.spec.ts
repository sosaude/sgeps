import { TestBed } from '@angular/core/testing';

import { OverviewFarmService } from './overview-farm.service';

describe('OverviewFarmService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: OverviewFarmService = TestBed.get(OverviewFarmService);
    expect(service).toBeTruthy();
  });
});
