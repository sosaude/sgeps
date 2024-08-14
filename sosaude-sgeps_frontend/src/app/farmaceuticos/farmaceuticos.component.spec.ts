import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FarmaceuticosComponent } from './farmaceuticos.component';

describe('FarmaceuticosComponent', () => {
  let component: FarmaceuticosComponent;
  let fixture: ComponentFixture<FarmaceuticosComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FarmaceuticosComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FarmaceuticosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
