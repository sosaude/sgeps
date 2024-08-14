import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UsOverviewComponent } from './us-overview.component';

describe('UsOverviewComponent', () => {
  let component: UsOverviewComponent;
  let fixture: ComponentFixture<UsOverviewComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UsOverviewComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UsOverviewComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
