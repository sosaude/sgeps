import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OverviewEmpresaComponent } from './overview-empresa.component';

describe('OverviewEmpresaComponent', () => {
  let component: OverviewEmpresaComponent;
  let fixture: ComponentFixture<OverviewEmpresaComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OverviewEmpresaComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OverviewEmpresaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
