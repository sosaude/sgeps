import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { GestaoServicosComponent } from './gestao-servicos.component';

describe('GestaoServicosComponent', () => {
  let component: GestaoServicosComponent;
  let fixture: ComponentFixture<GestaoServicosComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ GestaoServicosComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(GestaoServicosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
