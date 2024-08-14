import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthenticationService } from './_services/authentication.service';
import { MainUser } from './_models/all_user';
import { ConnectionService } from 'ng-connection-service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {
  currentUser: MainUser;
  title = 'internet-connection-check';
  status = 'ONLINE'; //initializing as online by default
  isConnected = true;

    constructor(
        private router: Router,
        private authenticationService: AuthenticationService,
        private connectionService:ConnectionService
    ) {
        this.authenticationService.currentUser.subscribe(x => this.currentUser = x);
        this.connectionService.monitor().subscribe(isConnected => {
          this.isConnected = isConnected;
          if(this.isConnected){
          // this.status = "ONLINE";
          } else {
            this.status = "Por favor, verifique a sua conexão à internet."
            alert(this.status);
          }
          });
    }

  }