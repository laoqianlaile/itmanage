<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class WebappController extends BaseController
{
    function xmlToArray($xmlFile)
    {

        $xml = simplexml_load_string($xmlFile);
        $jsonstr = json_encode($xml);
        $data = json_decode($jsonstr);
        $xml_array =array();
        $xml_array['formno'] =$data->FormNO;
        $xml_array['applytime'] =$data->ApplyTime;
        $xml_array['applyname'] =$data->ApplyName;
        $xml_array['phone'] =$data->PhonNo;
        $xml_array['depart'] =$data->SYRBuMen;
        $xml_array['office'] =$data->DeptNameshow;
        return $xml_array ;

    }
    public function test()
    {
        $xml22 ='<?xml version="1.0" encoding="utf-8" standalone="yes"?><contents><UUID>12119AB761434BA8A0803EE9ACA4F66F</UUID><DomainID>DBDB7066737E4C7A957D71FCC5F4FA29</DomainID><TimeStamp>636410015323985141</TimeStamp><Type>11</Type><Flag>0</Flag><IsEnabled>0</IsEnabled><IsSystem>0</IsSystem><Orders>0</Orders><DataVersion>8</DataVersion><TemplateID>BEB1397C720A42D98CF3F37CD51A7F70</TemplateID><TemplateFileName>20141128092320666314</TemplateFileName><DataName>张杨2017-9-14涉密网计算机撤销入网申请</DataName><DataFileName>20170918161146442796</DataFileName><PermitAttach>0</PermitAttach><PermitDoc>0</PermitDoc><PermitDispUnit>0</PermitDispUnit><DocName>张杨2017-9-14涉密网计算机撤销入网申请</DocName><DocFileName></DocFileName><MapFileName>20140805202522335900</MapFileName><FlowInstanceID>42FF16DA511C4d6dB00C8D545EF47D33</FlowInstanceID><Status>3</Status><MonitorOID></MonitorOID><MonitorName></MonitorName><CreateUserOID>AF5B7C5121064F70B22930D2026B276A</CreateUserOID><CreateUserID>zhangyang10@5y</CreateUserID><CreateUserName>张杨</CreateUserName><CreateTime>2017-9-14 15:58:52</CreateTime><Strategy></Strategy><UnitOID>DAA30B1170254025A6A53C76289C0294</UnitOID><UnitName>通信卫星事业部</UnitName><Description></Description><ApplyCash>0</ApplyCash><ConfirmCash>0</ConfirmCash><DisbursalCash>0</DisbursalCash><BillType>21</BillType><BudgetID>0</BudgetID><FormNO>WY/BM3.4-201709140046</FormNO><FormDeptName></FormDeptName><FormUnitName></FormUnitName><ApplyDate></ApplyDate><RealCostCash>0</RealCostCash><RealEngrossCash>0</RealEngrossCash><FlowFinishTime>2017-9-18 16:11:48</FlowFinishTime><CurrentNodeName>结束</CurrentNodeName><CurrentNodeID>EndNode</CurrentNodeID><Reason></Reason><Pznm></Pznm><PzCreateTime></PzCreateTime><Pzbh></Pzbh><PzYear></PzYear><PzState>0</PzState><IsPrint>0</IsPrint><FormDeptID>0</FormDeptID><LinkFormID></LinkFormID><LinkFormNo></LinkFormNo><RootID>12119AB761434BA8A0803EE9ACA4F66F</RootID><ParentID>12119AB761434BA8A0803EE9ACA4F66F</ParentID><RootNo></RootNo><ParentNO></ParentNO><TaskID></TaskID><FlowType>0</FlowType><AnnexNumber></AnnexNumber><OperationDate></OperationDate><ChargeCash></ChargeCash><OffsetAmount>0</OffsetAmount><EngrossOffsetAmount>0</EngrossOffsetAmount><PayState>0</PayState><ToOffset>0</ToOffset><RealLoanCash>0</RealLoanCash><ApplicantID></ApplicantID><ApplicantName></ApplicantName><SuperType>0</SuperType><SourceToOffset>0</SourceToOffset><SourceOffsetAmount>0</SourceOffsetAmount><SourceEngrossAmount>0</SourceEngrossAmount><TemplateVersion>7</TemplateVersion><FormID>12119AB761434BA8A0803EE9ACA4F66F</FormID><FormNo>WY/BM3.4-201709140046</FormNo><DomainID>DBDB7066737E4C7A957D71FCC5F4FA29</DomainID><BillType>21</BillType><DeptName></DeptName><DeptID></DeptID><UnitID></UnitID><UnitName></UnitName><ApplyDate>2017-9-14</ApplyDate><ApplyTime>2017-9-14</ApplyTime><ApplyName>安然</ApplyName><ApplyID>61CF486ED19040469F2DEFC5B1EDDC58</ApplyID><ApplyADID>tanran@5y</ApplyADID><SYRPosition>-</SYRPosition><SYRBuMen>通信卫星事业部</SYRBuMen><DeptNameshow>通信卫星总体研究室</DeptNameshow><DeptIDshow>DAA30B1170254025A6A53C76289C0294</DeptIDshow><ChargePerson>---</ChargePerson><ChargePersonID></ChargePersonID><ChargePersonADID></ChargePersonADID><ZRRPosition>---</ZRRPosition><ZRRBuMen>---</ZRRBuMen><UnitIDshow></UnitIDshow><Unit>---</Unit><MachineNo>J0105421(TX)</MachineNo><JHArea>航天城</JHArea><BuildingName>小卫星研发实验室（41号楼）</BuildingName><RoomNo>512</RoomNo><TelOffice>-</TelOffice><PhonNo>13261760620</PhonNo><IPAddress>10.64.126.62</IPAddress><MACAddress>8C89.A527.3FE9</MACAddress><ComputerName>tanran</ComputerName><IsCZ>0</IsCZ><IsSPGR>0</IsSPGR><Reason>毕业入职</Reason><AvidmCheckbox></AvidmCheckbox><OACheckbox></OACheckbox><PrintCheckbox></PrintCheckbox><ChuShiLeaderOpinion></ChuShiLeaderOpinion><SignChuShi>焦荣惠</SignChuShi><ChuShiSigntime>2017-9-15</ChuShiSigntime><ProductionDepOpinion>同意</ProductionDepOpinion><SignProduction>赵萌</SignProduction><ProductionSigntime>2017-9-15</ProductionSigntime><BaomiOpinion>同意</BaomiOpinion><SignBaomi>王向东</SignBaomi><BaomiSigntime>2017-9-18</BaomiSigntime><XXZXOpinion>同意</XXZXOpinion><SignXXZX>丁振鹏</SignXXZX><XXZXSigntime>2017-9-18</XXZXSigntime><IsIP></IsIP><SignAdmin></SignAdmin><AdminSigntime></AdminSigntime><SYRSectionID></SYRSectionID><ZRRSectionID></ZRRSectionID><AvidmCheckbox1></AvidmCheckbox1><OACheckbox1></OACheckbox1><PrintCheckbox1></PrintCheckbox1><Avidmid></Avidmid><Avidmpsd></Avidmpsd><SignAvidm></SignAvidm><AvidmSigntime></AvidmSigntime><OAid></OAid><OApsd></OApsd><SignOA></SignOA><OASigntime></OASigntime><OSTime>2015-6-15</OSTime><HardDiskNo>JE1YT3YK</HardDiskNo><Factory>联想</Factory><Model>M8000T</Model><GWDense>无</GWDense><SBDense>无</SBDense><YGType>0</YGType><GangWei>学生</GangWei><DanBaoOpinion></DanBaoOpinion><SignDanBao></SignDanBao><DanBaoSigntime></DanBaoSigntime><CheckType></CheckType><PersonType></PersonType><CurrentNodeName>结束</CurrentNodeName><CurrentNodeID>EndNode</CurrentNodeID></contents>';
        $xml= $this->xmlToArray($xml22);
//        $xml= $this->xmlToArray("C:\\wamp\\www\\itmanage\\trunk\\src\\ws\\bidaodan.xml");
        $this->assign('xml',$xml);
        $this->display();
    }
    public function pcchange()
    {
        $this->display();
    }
    public function testchange()
    {
        $this->display();
    }
    public function equipchange()
    {
        $this->display();
    }
    public function pcinfo(){
        $user = $_GET['username'];
        $zdtype = $_GET['zd_type'];
        $userid = M('person')->where("username='%s'",$user)->getField('id');
        $d_atpid = M('dictionary')->where("d_dictname='%s'and d_belongtype='资产类型'",$zdtype)->getField('d_atpid');
        $this->assign('userid',$userid);
        $this->assign('zdid',$d_atpid);
        $this->display();
    }
    public function equipinfo(){
        $user = $_GET['username'];
        $zdtype = $_GET['zd_type'];
        $userid = M('person')->where("username='%s'",$user)->getField('id');
        $d_atpid = M('dictionary')->where("d_dictname='%s'and d_belongtype='资产类型'",$zdtype)->getField('d_atpid');
        $this->assign('userid',$userid);
        $this->assign('zdid',$d_atpid);
        $this->display();
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='资产类型'
                 ";
        $sql_count="
                select
                    count(1) c
                from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='资产类型'";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.zd_atpstatus is null");
        if ("" != $queryparam['userid']){
            $searchcontent = trim($queryparam['userid']);
            $sql_select = $this->buildSql($sql_select,"du.zd_dutyman ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_dutyman ='".$searchcontent."'");
        }
        if ("" != $queryparam['zdid']){
            $searchcontent = trim($queryparam['zdid']);
            $sql_select = $this->buildSql($sql_select,"du.zd_type ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_type ='".$searchcontent."'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
        foreach($Result as $key=> &$value){
//            $value['zd_area'] = M("dictionary")->where(array("d_atpid"=>$value['zd_area'],"d_belongtype"=>'地区'))->getField("d_dictname");
            $value['zd_area'] = $this->getareaname($value['zd_area']);
            $value['zd_building'] = $this->getbuildingname($value['zd_belongfloor']);
            $value['zd_dutyman'] = $this->getusername($value['zd_dutyman']);
        }

        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    public function getData2(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='资产类型'
                 ";
        $sql_count="
                select
                    count(1) c
                from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='资产类型'";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.zd_atpstatus is null");
        if ("" != $queryparam['userid']){
            $searchcontent = trim($queryparam['userid']);
            $sql_select = $this->buildSql($sql_select,"du.zd_dutyman ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_dutyman ='".$searchcontent."'");
        }
        if ("" != $queryparam['zdid']){
            $searchcontent = trim($queryparam['zdid']);
            $sql_select = $this->buildSql($sql_select,"du.zd_type ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_type ='".$searchcontent."'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
        foreach($Result as $key=> &$value){
//            $value['zd_area'] = M("dictionary")->where(array("d_atpid"=>$value['zd_area'],"d_belongtype"=>'地区'))->getField("d_dictname");
            $value['zd_area'] = $this->getareaname($value['zd_area']);
            $value['zd_building'] = $this->getbuildingname($value['zd_belongfloor']);
            $value['zd_dutyman'] = $this->getusername($value['zd_dutyman']);
        }

        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    public function getbuildingname($id){
        $building =M('dictionary')->where("d_atpid='%s'",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getareaname($id){
        $building =M('dictionary')->where("d_atpid='%s'",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getusername($id){
        $building =M('person')->where("id='%s'",$id)->field('realusername')->find();
        return $building['realusername'];
    }
}

