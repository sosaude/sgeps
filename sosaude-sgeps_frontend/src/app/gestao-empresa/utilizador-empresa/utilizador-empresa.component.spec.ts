import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UtilizadorEmpresaComponent } from './utilizador-empresa.component';

describe('UtilizadorEmpresaComponent', () => {
  let component: UtilizadorEmpresaComponent;
  let fixture: ComponentFixture<UtilizadorEmpresaComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UtilizadorEmpresaComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UtilizadorEmpresaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
