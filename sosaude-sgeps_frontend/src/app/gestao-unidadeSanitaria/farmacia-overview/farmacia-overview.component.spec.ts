import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FarmaciaOverviewComponent } from './farmacia-overview.component';

describe('FarmaciaOverviewComponent', () => {
  let component: FarmaciaOverviewComponent;
  let fixture: ComponentFixture<FarmaciaOverviewComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FarmaciaOverviewComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FarmaciaOverviewComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
