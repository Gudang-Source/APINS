<?php 
require_once '../../../inc/db.php';
$kelas=$_REQUEST['kelas'];
$mpid=$_REQUEST['mp'];
$tapel=$_REQUEST['tapel'];
$smt=$_REQUEST['smt'];
$idp=$_REQUEST['pdid'];
$ab=substr($kelas, 0, 1);
	$nm=mysqli_fetch_array(mysqli_query($koneksi, "select * from siswa where peserta_didik_id='$idp'"));
	$adar=mysqli_num_rows(mysqli_query($koneksi, "select * from raport_k13 where id_pd='$idp' AND kelas='$ab' AND smt='$smt' AND tapel='$tapel' AND mapel='$mpid' and jns='k3'"));
		$sqkd=mysqli_query($koneksi, "select * from kd where kelas='$ab' and aspek='3' and mapel='$mpid'");
		$ra1=0;$ra2=0;
		while($ze=mysqli_fetch_array($sqkd)){
			$kd1=$ze['kd'];
			$sqln1=mysqli_fetch_array(mysqli_query($koneksi, "select avg(nilai) as rni from nh where id_pd='$idp' and smt='$smt' and tapel='$tapel' and mapel='$mpid' and kd='$kd1'"));
			$rph=$sqln1['rni'];
			$squts=mysqli_fetch_array(mysqli_query($koneksi, "select * from nuts where id_pd='$idp' and smt='$smt' and tapel='$tapel' and mapel='$mpid' and kd='$kd1'"));
			$sqas=mysqli_fetch_array(mysqli_query($koneksi, "select * from nats where id_pd='$idp' and smt='$smt' and tapel='$tapel' and mapel='$mpid' and kd='$kd1'"));
			if($rph>0 and $squts['nilai']>0 and $sqas['nilai']>0){
				$nak=(2*$rph+$squts['nilai']+$sqas['nilai'])/4;
			};
			if($rph==0){
				if(empty($squts['nilai'])){
					if(empty($sqas['nilai'])){
						$nak=0;
					}else{
						$nak=$sqas['nilai'];
					}
				}else{
					if(empty($sqas['nilai'])){
						$nak=$squts['nilai'];
					}else{
						$nak=($squts['nilai']+$sqas['nilai'])/2;
					}
				}
			}else{
				if(empty($squts['nilai'])){
					if(empty($sqas['nilai'])){
						$nak=$rph;
					}else{
						$nak=(2*$rph+$sqas['nilai'])/3;
					}
				}else{
					if(empty($sqas['nilai'])){
						$nak=(2*$rph+$squts['nilai'])/3;
					}else{
						$nak=(2*$rph+$squts['nilai']+$sqas['nilai'])/4;
					}
				}
			};
			$ra1=$ra1+$nak;
			if($nak>0){
				$ra2=$ra2+1;
			};
			$sqin=mysqli_query($koneksi, "select * from nh_temp where id_pd='$idp' and smt='$smt' and tapel='$tapel' and mapel='$mpid' and kd='$kd1'");
			$chin=mysqli_num_rows($sqin);
			if($chin>0){
				$sqin=mysqli_fetch_array(mysqli_query($koneksi, "select * from nh_temp where id_pd='$idp' and smt='$smt' and tapel='$tapel' and mapel='$mpid' and kd='$kd1'"));
				$idtemp=$sqin['id_nh'];
				if($nak>0){
					mysqli_query($koneksi, "update nh_temp set nph='$nak' where id_nh='$idtemp'");
				}else{
					mysqli_query($koneksi, "DELETE FROM nh_temp WHERE id_nh='$idtemp'");
				};
			}else{
				if($nak>0)
					mysqli_query($koneksi, "insert into nh_temp values('','$idp','$kelas','$smt','$tapel','$mpid','$kd1','$nak')");
			};
		}; //kd
		if($ra2>0){
			$nakhir=round($ra1/$ra2,0);
		}else{
			$nakhir=0;
		};
		$mkkm=mysqli_fetch_array(mysqli_query($koneksi, "select min(nilai) as kkmsekolah from kkm where tapel='$tapel'"));
		if(empty($mkkm['kkmsekolah'])){
			$kkmsaya=0;
		}else{
			$kkmsaya=$mkkm['kkmsekolah'];
		};
		$jarak=round((100-$kkmsaya)/3,0);
		$renA=100-$jarak;
		$renB=$renA-$jarak;
		$renC=$renB-$jarak;
		if($nakhir>$renA){
			$predikat="A";
		}elseif($nakhir>$renB){
			$predikat="B";
		}elseif($nakhir>$renC){
			$predikat="C";
		}else{
			$predikat="D";
		};
		$nakhir=number_format($nakhir,0);
		$smax=mysqli_fetch_array(mysqli_query($koneksi, "select * from nh_temp where id_pd='$idp' and smt='$smt' and tapel='$tapel' and mapel='$mpid' order by nph desc limit 1"));
		$smin=mysqli_fetch_array(mysqli_query($koneksi, "select * from nh_temp where id_pd='$idp' and smt='$smt' and tapel='$tapel' and mapel='$mpid' order by nph asc limit 1"));
		$kdmax=$smax['kd'];
		$kdx=mysqli_fetch_array(mysqli_query($koneksi, "select * from kd where kelas='$kelas' and aspek='3' and mapel='$mpid' and kd='$kdmax'"));
		$kdmin=$smin['kd'];
		$kdm=mysqli_fetch_array(mysqli_query($koneksi, "select * from kd where kelas='$kelas' and aspek='3' and mapel='$mpid' and kd='$kdmin'"));
		if($smax['nph']==$smin['nph']){
			if($smax['nph']>$renA){
				$desk="Alhamdulillah, Ananda ".$nm['nama']." Sangat Baik dalam ".$kdx['nama_kd'];
			}elseif($smax['nph']>$renB){
				$desk="Alhamdulillah, Ananda ".$nm['nama']." Baik dalam ".$kdx['nama_kd'];
			}elseif($smax['nph']>$renC){
				$desk="Alhamdulillah, Ananda ".$nm['nama']." Cukup Baik dalam ".$kdx['nama_kd'];
			}else{
				$desk="Alhamdulillah, Ananda ".$nm['nama']." Perlu Bimbingan dalam ".$kdx['nama_kd'];
			};
		}else{
			if($smax['nph']>$renA){
				$pmax=4;
				$dmax="Sangat Baik";
			}elseif($smax['nph']>$renB){
				$pmax=3;
				$dmax="Baik";
			}elseif($smax['nph']>$renC){
				$pmax=2;
				$dmax="Cukup Baik";
			}else{
				$pmax=1;
				$dmax="Perlu Bimbingan";
			};
			if($smin['nph']>$renA){
				$pmin=4;
				$dmin="Sangat Baik";
			}elseif($smin['nph']>$renB){
				$pmin=3;
				$dmin="Baik";
			}elseif($smin['nph']>$renC){
				$pmin=2;
				$dmin="Cukup Baik";
			}else{
				$pmin=1;
				$dmin="Perlu Bimbingan";
			};
			if($pmax==$pmin){
				$desk="Alhamdulillah, Ananda ".$nm['nama']." ".$dmax." dalam ".$kdx['nama_kd']." dan ".$kdm['nama_kd'];
			}else{
				$desk="Alhamdulillah, Ananda ".$nm['nama']." ".$dmax." dalam ".$kdx['nama_kd']." , ".$dmin." dalam ".$kdm['nama_kd'];
			};
		};
		$desk=mysqli_real_escape_string($koneksi,$desk);
		$ada=mysqli_num_rows(mysqli_query($koneksi, "select * from raport_k13 where id_pd='$idp' AND kelas='$ab' AND smt='$smt' AND tapel='$tapel' AND mapel='$mpid' and jns='k3'"));
		$ads=mysqli_num_rows(mysqli_query($koneksi, "select * from raport where id_pd='$idp' AND kelas='$ab' AND smt='$smt' AND tapel='$tapel' AND mapel='$mpid'"));
		if($ada>0){
			$srapor=mysqli_fetch_array(mysqli_query($koneksi, "select * from raport_k13 where id_pd='$idp' AND kelas='$ab' AND smt='$smt' AND tapel='$tapel' AND mapel='$mpid' and jns='k3'"));
			$idn=$srapor['id_raport'];
			mysqli_query($koneksi, "UPDATE raport_k13 SET nilai='$nakhir',predikat='$predikat',deskripsi='$desk' WHERE id_raport='$idn'");
			if($ads>0){
				$srapors=mysqli_fetch_array(mysqli_query($koneksi, "select * from raport where id_pd='$idp' AND kelas='$ab' AND smt='$smt' AND tapel='$tapel' AND mapel='$mpid'"));
				$idns=$srapors['id_raport'];
				mysqli_query($koneksi, "UPDATE raport SET nilai='$nakhir' WHERE id_raport='$idns'");
			}else{
				mysqli_query($koneksi, "INSERT INTO raport VALUES('','$idp','$ab','$smt','$tapel','$mpid','$nakhir','')");
			};
		}else{
			mysqli_query($koneksi, "INSERT INTO raport_k13 VALUES('','$idp','$ab','$smt','$tapel','$mpid','k3','$nakhir','$predikat','$desk')");
			if($ads>0){
				$srapors=mysqli_fetch_array(mysqli_query($koneksi, "select * from raport where id_pd='$idp' AND kelas='$ab' AND smt='$smt' AND tapel='$tapel' AND mapel='$mpid'"));
				$idns=$srapors['id_raport'];
				mysqli_query($koneksi, "UPDATE raport SET nilai='$nakhir' WHERE id_raport='$idns'");
			}else{
				mysqli_query($koneksi, "INSERT INTO raport VALUES('','$idp','$ab','$smt','$tapel','$mpid','$nakhir','')");
			};
		};
		mysqli_query($koneksi, "DELETE FROM nh_temp WHERE id_pd='$idp' and kelas='$ab' and smt='$smt' and tapel='$tapel' and mapel='$mpid'");
?>