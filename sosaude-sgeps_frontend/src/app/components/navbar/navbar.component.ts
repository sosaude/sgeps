import { Component, OnInit, ElementRef, OnDestroy } from '@angular/core';
import { Location, LocationStrategy, PathLocationStrategy } from '@angular/common';
import { Router } from '@angular/router';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent implements OnInit {
  userauth: any;
  userRole:any;
  seccao_codigo: number;
  
  constructor(private authenticationService: AuthenticationService, private router: Router
  ) {
    // redirect to home if already logged in
    if (this.authenticationService.currentUserValue) {
      this.userauth = this.authenticationService.currentUserValue.user;  
    this.userRole = this.authenticationService.currentUserValue.user.role.codigo;    
    // this.seccao_codigo = this.authenticationService.currentUserValue.user.role.seccao.codigo;    
    }
  }


  ngOnInit() {
    
  }


  sair() {
    Swal.fire({
      // title: 'Tem certeza?',
      text: "Tem certeza que deseja sair?",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: "#f15726",
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sim',
      reverseButtons: true
    }).then((result) => {
      if (result.value) {
        this.authenticationService.logout();
      }
    })
  }
}