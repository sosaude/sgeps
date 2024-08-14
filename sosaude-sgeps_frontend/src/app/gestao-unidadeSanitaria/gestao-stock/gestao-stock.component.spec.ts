import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { GestaoStockComponent } from './gestao-stock.component';

describe('GestaoStockComponent', () => {
  let component: GestaoStockComponent;
  let fixture: ComponentFixture<GestaoStockComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ GestaoStockComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(GestaoStockComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
