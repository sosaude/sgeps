import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UsUtilizadoresComponent } from './us-utilizadores.component';

describe('UsUtilizadoresComponent', () => {
  let component: UsUtilizadoresComponent;
  let fixture: ComponentFixture<UsUtilizadoresComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UsUtilizadoresComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UsUtilizadoresComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
