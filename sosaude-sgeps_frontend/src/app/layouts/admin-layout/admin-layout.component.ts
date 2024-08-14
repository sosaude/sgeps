import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { Location, LocationStrategy, PathLocationStrategy, PopStateEvent } from '@angular/common';
import { Router, NavigationEnd, NavigationStart } from '@angular/router';
import { Subscription } from 'rxjs';
import { MainUser } from 'src/app/_models/all_user';
import { AuthenticationService } from 'src/app/_services/authentication.service';

@Component({
  selector: 'app-admin-layout',
  templateUrl: './admin-layout.component.html',
  styleUrls: ['./admin-layout.component.scss']
})
export class AdminLayoutComponent implements OnInit {
  currentUser: MainUser;
  showNav: boolean;

  constructor(public location: Location, private router: Router, private authenticationService: AuthenticationService) {
    this.authenticationService.currentUser.subscribe(x => this.currentUser = x);
  }

  ngOnInit() {
  }
  // Used by the Bootstrap navbar-toggler button to hide/show
  // the nav in a collapsed state
  toggleNavBar(): void {
    this.showNav = !this.showNav;
  }

  // async signIn(): Promise<void> {
  //   await this.authenticationService.signIn();
  // }

  signOut(): void {
    this.authenticationService.logout();
  }
}
