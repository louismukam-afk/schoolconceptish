               <tr>
                        <td colspan="8"><strong style="font-size: 12px;">Unités d'enseignement Transversales\<span class="big-traductio">Common courses</span></strong></td>
                    </tr>
                    @foreach($releve->matieres as $matiere)
                            @if( ($matiere->type==1))
 <?php $sNote+= ($matiere->note*$matiere->credit);$subSnote+=$matiere->note*$matiere->credit; $sCoef+=$matiere->credit;$subScoef+=$matiere->credit;$TMP++; ?>
                                 @if($matiere->note>=50)
                                    <?php $TMV++; ?>
                                @endif
                                 <!--   <td> {{$matiere->id_code}}</td> -->

                                <td> {{$matiere->altName}}</td>
                    <td style="text-align: center">{{getSemestre($matiere->semestre)}}</td>
                              <td style="text-align: center">{{$matiere->credit}}</td>
                               <!-- <td>{{$matiere->note}}</td>-->
                        <td style="text-align: center">{{round($matiere->note/5,2)}} </td>
                        <td style="text-align: center">{{getGrade($matiere->note)[1]}}</td>
                        <td style="text-align: center">{{getGrade($matiere->note)[0]}}</td>
                    <td style="text-align: center">{{getMention($matiere->note,100)}}</td>
                                </tr>

                            @endif
                    @endforeach
                      <tr >
                    <td colspan="1"></td>
                    <td style="text-align: center;" >
                        <strong> Moy/Av </strong> 
                    </td>

                   <td style="text-align: center;">{{$subScoef}}</td>
                    @if($subScoef>0)
                    <td  style="text-align: center;">
                        <strong>{{ round(($subSnote/$subScoef)/5,2) }} </strong> 
                    </td>
                        <td style="text-align: center">{{getGrade($subSnote/$subScoef)[1]}}</td>
                    <td  style="text-align: center;">
                        <strong > {{getGrade(($subSnote/$subScoef))[0]}}  </strong> 
                    </td>
                     <td style="text-align: center">{{getMention($subSnote/$subScoef,100)}}</td> 
                      elseif($subScoef<0)
                      <td> <strong></strong></td>
                      <td> <strong></strong></td>
                      <td> <strong></strong></td>
                      <td> <strong></strong></td>
                      <td> <strong></strong></td>
                       @endif
                     <tr>