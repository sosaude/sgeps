import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import Swal from 'sweetalert2';
import {Location} from '@angular/common';

@Component({
  selector: 'app-change-password',
  templateUrl: './change-password.component.html',
  styleUrls: ['./change-password.component.scss']
})
export class ChangePasswordComponent implements OnInit {

  myForm: FormGroup;
  submitted: boolean = false;
  spinner: boolean = false;
  userauth: any;
  authValue:any;
  constructor(private formBuilder: FormBuilder, private router: Router, private authenticationService: AuthenticationService, private _location: Location) {
    if (this.authenticationService.currentUserValue) {
      this.authValue = this.authenticationService.currentUserValue;
      if (this.authenticationService.currentUserValue.user.role.codigo == 1) {
        // this.router.navigate(['/cliente']);
      }

      else if (this.authenticationService.currentUserValue.user.role.codigo == 2) {
        // this.router.navigate(['/agencias']);
      }

      else if (this.authenticationService.currentUserValue.user.role.codigo == 3) {
        // this.router.navigate(['/empresa']);
      }
      else if (this.authenticationService.currentUserValue.user.role.codigo == 4) {
        // this.router.navigate(['/empresa-verificacao']);
      }

      else if (this.authenticationService.currentUserValue.user.role.codigo == 5) {
        // this.router.navigate(['/eraser']);
      }
    }
   }


  ngOnInit() {
    this.myForm = this.formBuilder.group({
      password: ['', [Validators.required, Validators.minLength(7)]],
      password_atual: ['', [Validators.required, Validators.minLength(7)]]
    })
  }

  loginNewPassword() {
    this.submitted = true;
    if (this.myForm.invalid) {
      return;
    }

    if (this.myForm.valid) {
      this.spinner = true;
      let user = localStorage.getItem('userCred');
      this.userauth = JSON.parse(user);
      this.authenticationService.loginNewPassWord(this.myForm.controls.password.value, false).subscribe(data => {
        this.spinner = false;
        this.submitted = false;
        Swal.fire({
          title: 'Submetido com sucesso',
          text: "Senha actualizada com sucesso.",
          type: 'success',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        });
        
        this._location.back();
      },
        (error: any) => {
          this.spinner = false;
          // console.log(error)
        })

    }
  }

}
