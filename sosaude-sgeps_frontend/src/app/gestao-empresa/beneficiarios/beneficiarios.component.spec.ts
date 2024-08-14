import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BeneficiariosComponent } from './beneficiarios.component';

describe('BeneficiariosComponent', () => {
  let component: BeneficiariosComponent;
  let fixture: ComponentFixture<BeneficiariosComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BeneficiariosComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BeneficiariosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
