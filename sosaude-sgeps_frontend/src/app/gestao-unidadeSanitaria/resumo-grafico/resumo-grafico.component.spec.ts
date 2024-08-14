import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ResumoGraficoComponent } from './resumo-grafico.component';

describe('ResumoGraficoComponent', () => {
  let component: ResumoGraficoComponent;
  let fixture: ComponentFixture<ResumoGraficoComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ResumoGraficoComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ResumoGraficoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
