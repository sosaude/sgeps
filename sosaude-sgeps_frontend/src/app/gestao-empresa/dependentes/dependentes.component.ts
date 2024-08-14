import { Component, OnInit } from '@angular/core';
import { BeneficiariosService } from 'src/app/_services/beneficiarios.service';
import { DependentesService } from 'src/app/_services/dependentes.service';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators, FormArray, FormControl } from '@angular/forms';
import Swal from 'sweetalert2';
import { MatSlideToggleChange } from '@angular/material';
import * as moment from 'moment';
import { GruposService } from 'src/app/_services/grupos.service';
import * as XLSX from 'xlsx'; 
import { ExcelService } from 'src/app/_services/excel.service';



@Component({
  selector: 'app-dependentes',
  templateUrl: './dependentes.component.html',
  styleUrls: ['./dependentes.component.scss']
})
export class DependentesComponent implements OnInit {

  searchText: any;
  spinner: boolean = false;
  beneficiarios: any[];
  dependentes: any[] = [];

  p = 1;

  final_beneficiarios_array: any[];
  ficheiro: File = null;
  fileName= 'Beneficiarios.xlsx'; 

  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private excelService: ExcelService,
    private dp: DependentesService) {
  }

  ngOnInit() {
    this.getAllBene();
    this.getAll();
    // this.getQuantidadeBeneficiarios();
    
    
  }
  exportAsXLSX(): void {
    this.spinner = true;
    this.excelService.exportAsExcelFile(this.dependentes, 'Dependentes');
    this.spinner = false;
    // console.log(this.dependentes)
  }


  downloadExcel(): void 
  {
     /* table id is passed over here */   
     let element = document.getElementById('table-beneficiarios'); 
     const ws: XLSX.WorkSheet =XLSX.utils.table_to_sheet(element);

     /* generate workbook and add the worksheet */
     const wb: XLSX.WorkBook = XLSX.utils.book_new();
     XLSX.utils.book_append_sheet(wb, ws, 'Beneficiarios');

     /* save to file */
     XLSX.writeFile(wb, this.fileName);
    
  }



  getAll() {
    this.spinner = true;
    this.dp.getAll().subscribe(data => {
      // console.log(data);
      this.beneficiarios = Object(data)["data"];
      this.final_beneficiarios_array = Object(data)["data"];

      this.beneficiarios.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }
  getAllBene() {
    this.spinner = true;
    this.dp.getAllDep().subscribe(data => { 
      // console.log(data);
      this.dependentes = Object(data)["data"];
      // this.final_beneficiarios_array = Object(data)["data"];

      this.dependentes.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      
      // this.excelService.exportAsExcelFile(this.dependentes, 'Dependentes');
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  // getQuantidadeBeneficiarios() {
  //   this._grupo.getAll().subscribe(data => {
  //     this.grupos = Object(data)["data"]
  //     this.total_numero_beneficiarios = 0;
  //     this.grupos.forEach(element => {
  //       this.total_numero_beneficiarios = element.numero_beneficiarios + this.total_numero_beneficiarios;
  //     });
  //   }, error => {
  //     // console.log(error)
  //   })
  // }


// OnClick of button Upload
importarExcell(){
    this.spinner = true
    console.log(this.ficheiro);
    this.dp.upload(this.ficheiro).subscribe(
        (response) => {
          Swal.fire({
            title: 'Submetido.',
            text: "Criado com sucesso.",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: "#f15726",
            confirmButtonText: 'Ok'
          })
          document.getElementById('actualizarID_dependente').click();
          this.getAll()

          console.log(response)
        },
        error => {
          console.log(error)
          this.spinner = false
        }
    );
}


 

}

