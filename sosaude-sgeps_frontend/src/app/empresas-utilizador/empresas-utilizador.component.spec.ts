import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmpresasUtilizadorComponent } from './empresas-utilizador.component';

describe('EmpresasUtilizadorComponent', () => {
  let component: EmpresasUtilizadorComponent;
  let fixture: ComponentFixture<EmpresasUtilizadorComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EmpresasUtilizadorComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EmpresasUtilizadorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
