import { Injectable } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Location } from '@angular/common';

@Injectable()
export class ErrorInterceptor implements HttpInterceptor {
    constructor(private authenticationService: AuthenticationService, private _location: Location) { }

    intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        return next.handle(request).pipe(catchError(err => {
            if (err.status === 401) {
                // auto logout if 401 response returned from api
                this.authenticationService.logout();
                Swal.fire({
                    type: 'error',
                    title: 'Oops...',
                    confirmButtonColor: "#f15726",
                    text: 'Não tem permissões para aceder ao sistema!'
                }).then((result) => {
                    if (result.value) {
                        this.authenticationService.logout();
                        // location.reload();
                    }
                })
                // Swal.fire({
                //     type: 'error',
                //     title: 'Oops...',
                //     confirmButtonColor: "#f15726",
                //     text: `${err.error.error}`
                // })
            }
            else  if (err.status === 403) {
                // auto logout if 403 response returned from api
                Swal.fire({
                    type: 'error',
                    title: 'Oops...',
                    confirmButtonColor: "#f15726",
                    confirmButtonText: 'Voltar',
                    cancelButtonColor:'#2A3C52',
                    cancelButtonText: 'Sair',
                    showCancelButton: true,
                    showConfirmButton: true,
                    text: 'Não tem autorização para aceder esta página!'
                }).then((result) => {
                    // console.log(result);
                    
                    if (result.value) {
                        this._location.back();
                        // location.reload(true);
                    } else if (result.dismiss) {
                        this.authenticationService.logout();
                    }
                })
            }
            else if (err.status === 400) {
                // alert("Credencias invalidas!!")
                Swal.fire({
                    type: 'error',
                    title: 'Oops...',
                    confirmButtonColor: "#f15726",
                    text: `${err.error.error}`
                })
            }

            else if (err.status === 500) {
                // alert("Ocorreu algum erro! Tente de novo.")
                Swal.fire({
                    type: 'error',
                    title: 'Oops...',
                    confirmButtonColor: "#f15726",
                    text: `${err.error.error}`
                })
            }
            else if (err.status === 404) {
                // alert("Ocorreu algum erro! Tente de novo.")
                Swal.fire({
                    type: 'error',
                    title: 'Oops...',
                    confirmButtonColor: "#f15726",
                    text: `${err.error.message}`
                })
            }
            else if (err.status === 422) {
                for (let key in err.error.errors) {
                    let value = err.error.errors[key];
                    // console.log(value);
                    if (value) {
                        Swal.fire({
                            type: 'error',
                            title: 'Oops...',
                            confirmButtonColor: "#f15726",
                            text: `${value}`
                        })
                    }
                }


            }


            const error = err.error.message || err.statusText;
            return throwError(error);
        }))
    }
}