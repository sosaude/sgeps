import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { MainUser } from '../_models/all_user';
import {URL} from '../API_CONFIG';
import { Router } from '@angular/router';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    private currentUserSubject: BehaviorSubject<MainUser>;
    public currentUser: Observable<MainUser>;

    constructor(private http: HttpClient, private router: Router) {
        this.currentUserSubject = new BehaviorSubject<MainUser>(JSON.parse(localStorage.getItem('userCred')));
        this.currentUser = this.currentUserSubject.asObservable();
    }

    public get currentUserValue(): MainUser {
        return this.currentUserSubject.value;
    }

    login(identifier: string, password: string) {
        return this.http.post<any>(`${URL.url}/guest/login`, { identifier, password }, {headers: 
          {'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' } })
            .pipe(map(user => {
                // login successful if there's a jwt token in the response
                if (user && user.token) {
                    // store user details and jwt token in local storage to keep user logged in between page refreshes
                    localStorage.setItem('userCred', JSON.stringify(user));
                    this.currentUserSubject.next(user);
                }
                return user;
            }));
    }

    logout() {
        // remove user from local storage to log user out
        localStorage.removeItem('userCred');
        this.currentUserSubject.next(null);
        this.router.navigate(['/login']);
    }

    loginNewPassWord(password: string, forced:boolean) {
      let novo = {
        // user_id: id,
        password: password,
        forced: forced,
        // password_atual: password_atual
      };      
      return this.http.post<any>(`${URL.url}/guest/change_password`, novo)
      .pipe(map(user => {
        // login successful if there's a jwt token in the response
        if (user && user.token) {
            // store user details and jwt token in local storage to keep user logged in between page refreshes
            localStorage.setItem('userCred', JSON.stringify(user));
            this.currentUserSubject.next(user);
        }
  
          return user;
        })
      )
    }

    ResetPassWord(identifier: string){
      let identifier_send = {
        identifier:identifier
      };
      return this.http.post<any>(`${URL.url}/guest/forgot_password`, identifier_send)
    }
}