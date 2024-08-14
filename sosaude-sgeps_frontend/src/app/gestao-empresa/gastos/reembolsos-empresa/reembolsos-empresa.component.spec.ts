import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReembolsosEmpresaComponent } from './reembolsos-empresa.component';

describe('ReembolsosEmpresaComponent', () => {
  let component: ReembolsosEmpresaComponent;
  let fixture: ComponentFixture<ReembolsosEmpresaComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReembolsosEmpresaComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReembolsosEmpresaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
