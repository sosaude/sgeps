import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ServicosClinicaComponent } from './servicos-clinica.component';

describe('ServicosClinicaComponent', () => {
  let component: ServicosClinicaComponent;
  let fixture: ComponentFixture<ServicosClinicaComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ServicosClinicaComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ServicosClinicaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
