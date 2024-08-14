import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BaixasEmpresaComponent } from './baixas-empresa.component';

describe('BaixasEmpresaComponent', () => {
  let component: BaixasEmpresaComponent;
  let fixture: ComponentFixture<BaixasEmpresaComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BaixasEmpresaComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BaixasEmpresaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
