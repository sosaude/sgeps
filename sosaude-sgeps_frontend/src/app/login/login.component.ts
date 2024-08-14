import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators, FormControl, FormGroupDirective, NgForm } from '@angular/forms';
import { AuthenticationService } from '../_services/authentication.service';
import Swal from 'sweetalert2';
import { MainUser } from '../_models/all_user';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {
  loginForm: FormGroup;
  submitted: boolean = false;
  spinner: boolean = false;
  confirmarPasswordBoolean: boolean = false;
  loginNormal: boolean = true;
  myForm: FormGroup;
  userauth: MainUser;
  resetPasswordBoolean: boolean = false;
  resetPass: FormGroup;
  isChecked: boolean = false;

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private authenticationService: AuthenticationService,
  ) {
    // redirect to home if already logged in
    if (this.authenticationService.currentUserValue) {
      
      if (this.authenticationService.currentUserValue.user.role.seccao.code == 1) {
        this.router.navigate(['/farmacias']);
      }

      else if (this.authenticationService.currentUserValue.user.role.seccao.code == 2) {
        this.router.navigate(['/empresa/overview']);
      }

      else if (this.authenticationService.currentUserValue.user.role.seccao.code == 3) {
        this.router.navigate(['/unidade-sanitaria/farm-geral']);
      }
      else if (this.authenticationService.currentUserValue.user.role.seccao.code == 4) {
        this.router.navigate(['/unidade-sanitaria/us-geral']);
      }
    }
  }

  ngOnInit() {
    this.loginForm = this.formBuilder.group({
      identifier: ['', [Validators.required]],
      password: ['', Validators.required]
    });

    this.confirmationPassword();
    this.resetPasswordSendEmail();
  }

  resetPasswordSendEmail() {
    this.resetPass = this.formBuilder.group({
      identifier: ['', Validators.required]
    });
  }

  back(){
    this.router.navigate(['/login'])
  }

  confirmationPassword() {
    this.myForm = this.formBuilder.group({
      new_password: ['', [Validators.required, Validators.minLength(7)]],
      confirmPassword: ['', [Validators.required]]
    }, { validator: this.checkPasswords });

  }

  checkPasswords(group: FormGroup) { // here we have the 'passwords' group
    let pass = group.controls.new_password.value;
    let confirmPass = group.controls.confirmPassword.value;

    return pass === confirmPass ? null : { notSame: true }
  }

  onSubmit() {
    this.submitted = true;
    if (this.loginForm.invalid) {
      return;
    }

    if (this.loginForm.valid) {
      this.spinner = true;

      this.authenticationService.login(this.loginForm.controls.identifier.value, this.loginForm.controls.password.value).subscribe(data => {
        this.spinner = false;
        let user = data.user;
        this.userauth = data;

        if (user.loged_once) {
          if (user.role.seccao.code == 1) { //ADMIN
            this.router.navigate(['/farmacias']);
          }

          else if (user.role.seccao.code == 2) { // UTILIZADOR DE EMPRESA
            this.router.navigate(['/empresa/overview']);
          }

          else if (user.role.seccao.code == 3) { // UTILIZADOR DE FARMACIA
            this.router.navigate(['/unidade-sanitaria/farm-geral']);
          }
          else if (user.role.seccao.code == 4) { // UTILIZADOR DE UNIDADE SANITÁRIA
            this.router.navigate(['/unidade-sanitaria/us-geral']);
          }
          else {
            Swal.fire({
              title: 'Atenção!',
              text: "Não tem autorização para aceder.",
              type: 'warning',
              showCancelButton: false,
              confirmButtonColor: "#f15726",
              confirmButtonText: 'Ok'
            })
            this.authenticationService.logout();
            location.reload(true);
          }
        }

        else if (!user.loged_once) {
          this.confirmarPasswordBoolean = true;
          this.loginNormal = false;
        }

      },
        error => {
          this.spinner = false;
          // console.log(error)
        })
    }
  }


  loginNewPassword() {
    this.submitted = true;
  if (this.myForm.invalid) {
      return;
    }

    if (this.myForm.valid) {
      this.spinner = true;
      // let user = localStorage.getItem('userCred')
      // this.userauth = JSON.parse(user);

      this.authenticationService.loginNewPassWord(this.myForm.controls.new_password.value, true).subscribe(data => {

        this.spinner = false;
        this.submitted = false;
        if (this.userauth.user.role.seccao.code == 1) { //ADMIN
          this.router.navigate(['/farmacias']);
        }

        else if (this.userauth.user.role.seccao.code == 2) { // UTILIZADOR DE EMPRESA
          this.router.navigate(['/empresa/overview']);
        }

        else if (this.userauth.user.role.seccao.code == 3) { // UTILIZADOR DE FARMACIA
          this.router.navigate(['/unidade-sanitaria/farm-geral']);
        }
        else if (this.userauth.user.role.seccao.code == 4) { // UTILIZADOR DE UNIDADE SANITÁRIA
          this.router.navigate(['/unidade-sanitaria/us-geral']);
        }

        else {
          Swal.fire({
            title: 'Atenção!',
            text: "Não tem autorização para aceder.",
            type: 'warning',
            showCancelButton: false,
            confirmButtonColor: "#f15726",
            confirmButtonText: 'Ok'
          })
          this.authenticationService.logout();
          location.reload(true);
        }
      },
        (error: any) => {
          this.spinner = false;
          // console.log(error)
        })
    }
  }

  forgotPassword() {
    this.confirmarPasswordBoolean = false;
    this.loginNormal = false;
    this.resetPasswordBoolean = true;
  }

  voltar() {
    this.confirmarPasswordBoolean = false;
    this.loginNormal = true;
    this.resetPasswordBoolean = false;
  }

  resetPasswordEmail() {
    this.submitted = true;
    if (this.resetPass.invalid) {
      return;
    }

    if (this.resetPass.valid) {
      this.spinner = true;
      this.authenticationService.ResetPassWord(this.resetPass.controls.identifier.value).subscribe(data => {
        // // console.log(data)
        this.spinner = false;
        this.submitted = false;
        Swal.fire({
          position: 'center',
          type: 'success',
          title: 'Obrigado, será contactado pelo administrador brevemente!!',
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok',
          showConfirmButton: true
          // timer: 5000
        }).then((result) => {
          if (result.value) {
            location.reload(true);
            // this.router.navigate['']
          }
        })

      }, (error: any) => {
        this.spinner = false;
        // console.log(error)
      })
    }
  }

  fieldsChange(values: any) {
    this.isChecked = values.currentTarget.checked;
  }
}