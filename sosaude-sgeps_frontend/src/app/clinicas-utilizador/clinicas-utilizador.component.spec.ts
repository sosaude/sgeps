import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ClinicasUtilizadorComponent } from './clinicas-utilizador.component';

describe('ClinicasUtilizadorComponent', () => {
  let component: ClinicasUtilizadorComponent;
  let fixture: ComponentFixture<ClinicasUtilizadorComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ClinicasUtilizadorComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ClinicasUtilizadorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
