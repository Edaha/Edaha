// palette_selfy.js .. for PaintBBS and ShiPainter .. last update : 2004/04/11.

//���g���� .. �O��JS�Ƃ��ēǂݍ���ł���A�D���ȏ��� palette_selfy() ���Ăяo���ĉ�����.
		var selfv=new Array();	var selfytag=new Array();	//�������Ȃ���.

//���ݒ� ------------------------------------------------------------
//  ��selfv[*] �́A���ꂼ��̐ݒ���󔒂ɂ���ƁA���̋@�\�̃{�^����\�������Ȃ��ł��܂�.

// +-����l�̂Ƃ�
var pnum = 10;	// +- �̃f�t�H���g�l
selfv[0] = 'size=3 style="text-align:right">';	// ���l�^�O(type=text)�̒��g


// �p���b�g���X�g.
// ..�e�v�f�̒��̐F�́A1�����Ȃ瑼��13�F�͂��̐F�����Ɂ���2�ʂ肩�玩���擾�A
var psx = 0;	// 0:�ʓx+���x������. 1:�F�����z��.

// �ʓx+���x�����ɂ���Ƃ��̐F. (�����ݒ肷��ꍇ�́A1��1�̐F�� \n �ŋ�؂�)
var pdefs = new Array(
	'#ffffff',
	'#ffe6e6','#ffece6','#fff3e6','#fff9e6','#ffffe6',
	'#f3ffe6','#e6fff3','#e6f3ff','#ffe6ff','#eeddbb',
'');	// ���󔒂��Ƃ��̗v�f�̓X�L�b�v.

// �F���ŏz������Ƃ��̐F. (�����ݒ肷��ꍇ�́A1��1�̐F�� \n �ŋ�؂�)
var pdefx = new Array(
	'#ffffff',
	'#ffe6e6','#ffcccc','#ff9999','#e6cccc','#e69999',
	'#cc9999','#cc6666','#996666','#993333','#660000',
'');	// ���󔒂��Ƃ��̗v�f�̓X�L�b�v.


// �f�t�H���g�̃p���b�g�J���[ (��ԍŏ��ɃA�v���b�g�ɂł�F)
var pbase = '#000000\n#FFFFFF\n#B47575\n#888888\n#FA9696\n#C096C0\n#FFB6FF\n#8080FF\n#25C7C9\n#E7E58D\n#E7962D\n#99CB7B\n#FCECE2\n#F9DDCF';


// �T���v���J���[��
  // �\������p���b�g�̃J���[�ԍ�(���̒��ɂ���ԍ��������ŏ����o��)
var sams = new Array(0,2,4,6,8,10,12,1,3,5,7,9,11,13);	// �ʓx+���x�����ɂ���Ƃ�
var samx = new Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13);	// �F���ŏz������Ƃ�

selfv[1] = '&nbsp;';	// �t�H���g
selfv[2] = 'style="font-size:xx-small; background-color:$FONT;"';
	// �t�H���g�^�O�̒��g(�u$FONT�v:16�i�@RGB�F������A���m�ɓK�p����鑮���́���3��)
	//  �c color, style="color" style="background", style="background-color")

		// ���������艺�� ">"(���^�O) ������Ă������� //

// �p���b�g�̑I���{�^��(type=radio)�^�O�̒��g
selfv[3] = 'style="border-width:0;" title="�f�t�H���g�̃p���b�g">';	// �f�t�H���g�F
selfv[4] = 'style="border-width:0;" title="�����̃p���b�g���g���B\n�`�F�b�N���Ă�Ƃ��ɂ���ɉ����ƁA�`�F�b�N���O��A\n�F���z���A�ʓx+���x�p���b�g�ɁB(1�Ԃ̐F����{�F)">';	// �I��


// �{�^��(type=button)�^�O�̒��g
selfv[5] = 'value="H" title="�F���p���b�g (1�Ԃ̐F����{�F)">';	// �F��
selfv[6] = 'value="S" title="�ʓx�p���b�g (1�Ԃ̐F����{�F)">';	// �ʓx
selfv[7] = 'value="B" title="���x�p���b�g (1�Ԃ̐F����{�F)">\n';	// ���x
selfv[8] = 'value="o" title="�����ɍ��̃p���b�g��ۑ�">';	// �Z�[�u
selfv[9] = 'value="x" title="�����̃p���b�g���f�t�H���g�ɖ߂�"><br>\n';	// �f�t�H���g

selfv[10] = 'value="H+" title="�p���b�g�S�̂̐F�����{">';	// �F��+
selfv[11] = 'value="H-" title="�p���b�g�S�̂̐F�����|">';	// �F��-
selfv[12] = 'value="S+" title="�p���b�g�S�̂̍ʓx���{">';	// �ʓx+
selfv[13] = 'value="S-" title="�p���b�g�S�̂̍ʓx���|">';	// �ʓx-
selfv[14] = 'value="B+" title="�p���b�g�S�̖̂��x���{">\n';	// ���x+
selfv[15] = 'value="B-" title="�p���b�g�S�̖̂��x���|">\n';	// ���x-
selfv[16] = 'value="RGB+" title="�p���b�g�S�̂�RGB���{"><br>\n';	// RGB+
selfv[17] = 'value="RGB-" title="�p���b�g�S�̂�RGB���|"><br>\n';	// RGB-


// �O���f�[�V�����̂Ƃ�
selfv[18] = 'style="border-width:0;" title="2�_�����ɃO���f�[�V���� (1�Ԃ̐F��14�Ԃ̐F)" checked>2';	// 2�_
selfv[19] = 'style="border-width:0;" title="3�_�����ɃO���f�[�V���� (1�ԁA8�ԁA14�Ԃ̐F)">3';	// 3�_
selfv[20] = 'style="border-width:0;" title="4�_�����ɃO���f�[�V���� (1�A6�A10�A14�Ԃ̐F)">4<br>\n';	// 4�_
selfv[21] = 'value="RGB" title="RGB�ŃO���f�[�V����">\n';	// RGB?
selfv[22] = 'value="+HSB" title="+HSB�ŃO���f�[�V���� (�F���{����)">\n';	// +HSB
selfv[23] = 'value="-HSB" title="-HSB�ŃO���f�[�V���� (�F���|����)"><br>\n';	// -HSB


// �ǉ��E�폜
selfv[24] = 'value="+" title="�p���b�g��ǉ����܂�">';	// �ǉ�
selfv[25] = 'value="-" title="�I�𒆂̃p���b�g���폜���܂�">\n';	// �폜


// �Z�[�u�E�I�[�g�Z�[�u
selfv[26] = 'checked title="�����Ƀ`�F�b�N�����Ă����ƁA�F��ύX����Ƃ��A\n�@�����ŕۑ��p���b�g�Ƀp���b�g�����Z�[�u���܂��B\n�����ۑ����K�p�����̂́A\n�@�`�F�b�N���Ă�p���b�g���瑼�̃p���b�g�Ɉړ������Ƃ��A\n�@�`�F�b�N���Ă�p���b�g��H/S/B�{�^�����������Ƃ��A\n�@��2�̂Ƃ��ł��B�T���v���̐F���ς��܂��B\n�������A�����Ƀ`�F�b�N���ĂȂ��Ă��A\n�@�蓮�ŃZ�[�u�{�^���������΁A�p���b�g�ɕۑ�����܂��B">';	// �����Z�[�u
selfv[27] = 'value="O" title="���̑S�̂̃p���b�g���N�b�L�[�ɕۑ�"><br>\n';	// �Z�[�u


// �f�t�H���g�̃p���b�g�� �F��360���ɂ��邩�A�ʓx++���x-- �ɂ��邩
selfv[28] = 'style="border-width:0;" title="�f�t�H���g�̃p���b�g�́A�F���ŏz������">H<sup>o</sup>';	// H��
selfv[29] = 'style="border-width:0;" title="�f�t�H���g�̃p���b�g�́A�ʓx�{�A���x�|�Ń��X�g">+S-B';	// +S-B
selfv[30] = 'value="X" title="�S�̂̃p���b�g���f�t�H���g�ɖ߂�"><br>\n';	// �f�t�H���g��


// UPLOAD / DOWNLOAD
selfv[31] = 'value="" size=8 title="�p���b�g�f�[�^�B\n�E�A�b�v���[�h����Ƃ��́A�����ɓ\��t���Ă��������B\n�E�_�E�����[�h����Ƃ��́A�����Ƀf�[�^���o�͂���܂��B\n�@ ���[�J���̃e�L�X�g�ɂł��ۑ����Ă��������B\n���p���b�g�f�[�^�́A\n pals = new Array(\'#FFFFFF\',\'#B47575\\n#888888\\n...\');\n�@�̂悤�ɁAJS�̔z��`���ŏ�����܂��B">\n';	// 
selfv[32] = 'value="��" title="������p���b�g�f�[�^���A�b�v���[�h">';	// 
selfv[33] = 'value="��" title="�����Ƀp���b�g�f�[�^���_�E�����[�h"><br>\n';	// 


// ���̃p���b�g�e�[�u�����͂�ł�A�\�[�X�Ƃ��^�O�Ƃ� (form�^�O�͏�������_��)
// �t�H�[���n�܂�
selfytag[0] = '<table class="ptable"><tr><form name="palepale"><td class="ptd" nowrap>\n<div align=right class="menu">Palette-Selfy</div>\n<div style="font-size:xx-small;">\n';

// �t�H�[���̊�_1 (�ʂ̃p���b�g  �`  �S�̂� HSB�ARGB +- �Ƃ�)
selfytag[1] = '<div style="text-align:right; padding:5;">\n';

// �t�H�[���̊�_2 (�S�̂� HSB�ARGB +- �Ƃ�  �`  �O���f�[�V����)
selfytag[2] = '</div>\n<div style="text-align:right; padding:0 5 5 5;"">\nGradation';

// �t�H�[���̊�_3 (�O���f�[�V���� �` �p���b�g�̒ǉ��E�폜�{�^��)
selfytag[3] = '</div>\n<div style="text-align:right; padding:0 5 0 5;"">\nPalette';

// �t�H�[���̊�_4 (�p���b�g�̒ǉ��E�폜�{�^�� �` �Z�[�u�{�^��)
selfytag[4] = '\nSave';

// �t�H�[���̊�_5 (�Z�[�u�{�^�� �` Default�� H++/+S-B �ǂ��炩)
selfytag[5] = '</div>\n<div style="text-align:right; padding:3 5 2 5;"">\nDefault';

// �t�H�[���̊�_6 (Default�� H++/+S-B �ǂ��炩 �` �p���b�g�̃A�b�v/�_�E�����[�h)
selfytag[6] = '</div>\n<div style="text-align:right; padding:0 5 0 5;">\nUpdata ';

// �t�H�[���I���
selfytag[7] = '</div>\n</div>\n</td></form></tr></table>\n';
//���ݒ肨��� ------------------------------------------------------


// �����l (������Ƃ���Ŏg���l)
var d = document;
var pon,pno;	// radio�`�F�b�N���H  / �`�F�b�N�����p���b�gNO.
var qon,qno,qmo;	// button�v�b�V�����H / �v�b�V�������p���b�gNO.
var pals = new Array();	// color-palette
var inp = '<input type="button" ';	// input-button
var inr = '<input type="radio" ';	// input-button
var cname = 'selfy=';	// cookie-name
var psx_ch = new Array('','');	// h_sb-checked
var brwz=0;
if(d.all){ brwz=1; }else if(d.getElementById){ brwz=2; }


// -------------------------------------------------------------------------
// HSB��RGB �v�Z. �l��0�`255.
function HSBtoRGB(h,s,v){
	var r,g,b;
	if(s==0){
		r=v; g=v; b=v;
	}else{
		var max,min,dif;
		h*=360/255;	//360��
		max=v;
		dif=v*s/255;	//s=(dif/max)*255
		min=v-dif;  //min=max-dif

		if(h<60){
			r=max;	b=min;	g=h*dif/60+min;
		}else if(h<120){
			g=max;	b=min;	r=-(h-120)*dif/60+min;
		}else if(h<180){
			g=max;	r=min;	b= (h-120)*dif/60+min;
		}else if(h<240){
			b=max;	r=min;	g=-(h-240)*dif/60+min;
		}else if(h<300){
			b=max;	g=min;	r= (h-240)*dif/60+min;
		}else if(h<=360){
			r=max;	g=min;	b=-(h-360)*dif/60+min;
		}else{r=0;g=0;b=0;}
	}
	return(new Array(r,g,b));
}


// RGB��HSB �v�Z. �l��0�`255.
function RGBtoHSB(r,g,b){
	var max,min,dif,h,s,v;

	// max
	if(r>=g && r>=b){
		max=r;
	}else if(g>=b){
		max=g;
	}else{
		max=b;
	}

	// min
	if(r<=g && r<=b){
		min=r;
	}else if(g<=b){
		min=g;
	}else{
		min=b;
	}

	// 0,0,0
	if(max<=0){ return(new Array(0,0,0)); }

	// difference
	dif=max-min;

	//Hue:
	if(max>min){
		if(g==max){
			h=(b-r)/dif*60+120;
		}else if(b==max){
			h=(r-g)/dif*60+240;
		}else if(b>g){
			h=(g-b)/dif*60+360;
		}else{
			h=(g-b)/dif*60;
		}
		if(h<0){
			h=h+360;
		}
	}else{ h=0; }
	h*=255/360;

	//Saturation:
	s=(dif/max)*255;

	//Value:
	v=max;

	return(new Array(h,s,v));
}


// RGB16��RGB10 �\�L. �l�� 000000�`ffffff
function to10rgb(str){
	var ns = new Array();
	str = str.replace(/[^0-9a-fA-F]/g,'');
	for(var i=0; i<=2; i++){
		ns[i] = str.substr(i*2,2);
		if(!ns[i]){ ns[i]='00'; }
		ns[i] = Number(parseInt(ns[i],16).toString(10));
	}
	return(ns);
}


// 10��16�i�@��
function format16(n){
	n = Number(n).toString(16);
	if(n.length<2){ n='0'+n; }
	return(n);
}




// -------------------------------------------------------------------------
// �p���b�g�� (�� q=1:�A�v���b�g�p���b�g�ɏo�͂��Ȃ�. lst=1:�ŏ��̂Ƃ�
function rady(p,q,lst){
	var d = document;
	var df = d.forms.palepale;

	// �f�t�H���g�p���b�g
	if(!p&&p!=0){ pon=0; pno=''; d.paintbbs.setColors(pbase); return; }

	var ps = pals[p].split('\n');
	var n = pnum;
	if(!q && df.num.value){ n = Number(df.num.value); }
	if(!q && pon==1 && pno!=p){ poncheck(); }

	// �����Ă�Ȃ炷���Ԃ�
	if((pon!=1 || pno!=p) && ps.length==14){
		if(!q){ pon=1; pno=p; }
		if(q!=1 && pals[p]){ d.paintbbs.setColors(pals[p]); } return;
	}

	// check���Ă�Ȃ�
	if(pon==1 && pno==p){
		var pget = String(d.paintbbs.getColors());
//		if(pget==pals[p]){ return; }
		var cs = pget.split('\n');
		ps[0] = cs[0];  ps[1] = '';
	}
	// �����Ă���Ȃ�
	var cs = new Array();

	var psy=0;	// H��/ +S-B
	psy = check_h_sb(lst);

	if(psy==1){ cs = rh_list(p,n); }// H�����X�g
	else{ cs = sb_list(p,n); }	// +S-B ���X�g

	if(q){	// �����ݒ莞
		pals[p] = String(cs.join('\n'));
	}
	if(q!=1){	// ���
		if(pon==1 && pno==p){ checkout(); }
		else{ pon=1; pno=p; }
//		pals[p] = String(cs.join('\n'));
		d.paintbbs.setColors(String(cs.join('\n')));
	}
}


// H�����X�g
function rh_list(p,n){
	var ps = pals[p].split('\n');
	var rgb = to10rgb(ps[0]);	//��RGB
	var hsv = RGBtoHSB(rgb[0],rgb[1],rgb[2]);	//��HSB
	var cs = new Array(ps[0],ps[1]);
	if(!cs[0]){ cs[0]='#ffffff'; }
	if(hsv[1]!=0 && !cs[13]){ cs[13]='#ffffff'; }

	for (var i=1; i<13; i++){
		if(ps[i] && (pon!=1 || pno!=p)){ cs[i]=ps[i]; continue; }	//����
		var x,y,z;
		if(hsv[1]==0){	//����
			x = hsv[0];
			y = 0;
			if(i%2==0){ z = 255-i*n; }else{ z = 0+(i-1)*n; }
		}else if(i>=12){
			x = hsv[0];
			y = 0;
			z = 255-hsv[1];
		}else{
			x = hsv[0] + i*255/12;
			y = hsv[1];
			z = hsv[2];
		}
		while(x<0){ x+=255; }	if(y<0){ y=0; }	if(z<0){ z=0; }	//��0
		while(x>255){ x-=255; }	if(y>255){ y=255; }	if(z>255){ z=255; }	//��255
//		for (var j=0; j<=2; j++){ hsv[j] = Math.round(hsv[j]); }
		rgb = HSBtoRGB(x,y,z);
		for (var j=0; j<=2; j++){ rgb[j] = Math.round(rgb[j]); }
		cs[i] = '#'+format16(rgb[0])+format16(rgb[1])+format16(rgb[2]);
	}
	return(cs);
}


// +S-B ���X�g
function sb_list(p,n){
	var ps = pals[p].split('\n');
	var rgb = to10rgb(ps[0]);	//��RGB
	var hsv = RGBtoHSB(rgb[0],rgb[1],rgb[2]);	//��HSB
	var cs = new Array(ps[0],ps[1]);
	if(!cs[0]){ cs[0]='#ffffff'; }
	if(hsv[1]==0 && !cs[1]){ cs[1]='#000000'; }
	else if(!cs[1]){ cs[1]='#ffffff'; }

	for (var i=2; i<14; i++){
		if(ps[i] && (pon!=1 || pno!=p)){ cs[i]=ps[i]; continue; }	//����
		var y,z;
		if(hsv[1]==0){	//����
			y = 0;
			if(i%2==0){ z = 255-i*n; }else{ z = 0+(i-1)*n; }
		}else{
			if(i%2==0){	//��
				y = hsv[1]+i*n;
				z = hsv[2];
			}else{	//�E
				y = hsv[1]+(i-1)*n;
				z = hsv[2]-(i-1)*n;
			}
		}
		while(z<0){ z+=255; }	while(y<0){ y+=255; }	//��0
		while(z>255){ z-=255; }	while(y>255){ y-=255; }	//��255
//		for (var j=0; j<=2; j++){ hsv[j] = Math.round(hsv[j]); }
		rgb = HSBtoRGB(hsv[0],y,z);
		for (var j=0; j<=2; j++){ rgb[j] = Math.round(rgb[j]); }
		cs[i] = '#'+format16(rgb[0])+format16(rgb[1])+format16(rgb[2]);
	}
	return(cs);
}


// �ʂ�H/S/B�����X�g�A�b�v
function onplus(p,m){
	var d = document;
	var df = d.forms.palepale;
	var n = Number(df.num.value);	//+-
	if(pon==1 && pno==p){ poncheck(); }

	// �A���̂Ƃ�
	if(m>0 && n*(qon+1)>38){ qon=0; }
	if(qno==p && qmo==m && qon>=1){ qon++; n*=(qon+1)/2; }
	else{ qno=p; qmo=m; qon=1; }

	var ps = pals[p].split('\n');
	var rgb = to10rgb(ps[0]);	//��RGB
	var hsv = RGBtoHSB(rgb[0],rgb[1],rgb[2]);	//��HSB
	var cs = new Array();
	if(m==2){ n*=-1; }
	for (var i=0; i<14; i++){
		var z;
		if(m==0){ z = hsv[m]+((i%2)*2-1)*Math.round(Math.floor(i/2)*(n)); }
		else{ z = hsv[m]+i*n; }
		while(z<0){ z+=255; }	//��0
		while(z>255){ z-=255; }	//��255
//		for (var j=0; j<=2; j++){ hsv[j] = Math.round(hsv[j]); }
		if(m==1){ rgb = HSBtoRGB(hsv[0],z,hsv[2]); }	//��HSB
		else if(m==2){ rgb = HSBtoRGB(hsv[0],hsv[1],z); }
		else{ rgb = HSBtoRGB(z,hsv[1],hsv[2]); }	//��HSB
		for (var j=0; j<=2; j++){ rgb[j] = Math.round(rgb[j]); }
		cs[i] = '#'+format16(rgb[0])+format16(rgb[1])+format16(rgb[2]);
	}
	checkout(1);
	d.paintbbs.setColors(String(cs.join('\n')));
}


// �S�̂�H/S/B���v���X�}�C�i�X
function alplus(m,n){
	var d = document;
	var cs = String(d.paintbbs.getColors()).split('\n');
	n *= Number(d.forms.palepale.num.value);	//+-
	poncheck();

	for (var i=0; i<cs.length; i++){
		var rgb = to10rgb(cs[i]);	//��RGB
		var hsv = RGBtoHSB(rgb[0],rgb[1],rgb[2]);	//��HSB
		//�����x255�̂Ƃ��ʓx��
		if(m==2 && n>0 && hsv[2]>=255){
			hsv[1] -= n;
			if(hsv[1]<0){ hsv[1]=0; }else if(hsv[1]>255){ hsv[1]=255; }	//��0 or ��255
		}
		hsv[m] += n;
		//��0 ��255
		if(m==0){
			if(hsv[0]<0){ hsv[0]+=255; }else if(hsv[0]>255){ hsv[0]-=255; }
		}else{
			if(hsv[m]<0){ hsv[m]=0; }else if(hsv[m]>255){ hsv[m]=255; }
		}
//		for (var j=0; j<=2; j++){ hsv[j] = Math.round(hsv[j]); }
		rgb = HSBtoRGB(hsv[0],hsv[1],hsv[2]);	//��HSB
		for (var j=0; j<=2; j++){ rgb[j] = Math.round(rgb[j]); }
		cs[i] = '#'+format16(rgb[0])+format16(rgb[1])+format16(rgb[2]);
	}
	checkout();
	d.paintbbs.setColors(String(cs.join('\n')));
}


// �S�̂�RGB���v���X�}�C�i�X
function alrgb(n){
	var d = document;
	var cs = String(d.paintbbs.getColors()).split('\n');
	n *= Number(d.forms.palepale.num.value);	//+-
	poncheck();

	for (var i=0; i<cs.length; i++){
		var rgb = to10rgb(cs[i]);	//��RGB
		for (var j=0; j<=2; j++){
			rgb[j] += n;
			rgb[j] = Math.round(rgb[j]);
			if(rgb[j]<0){ rgb[j]=0; }	//��0
			if(rgb[j]>255){ rgb[j]=255; }	//��255
		}
		cs[i] = '#'+format16(rgb[0])+format16(rgb[1])+format16(rgb[2]);
	}
	checkout();
	d.paintbbs.setColors(String(cs.join('\n')));
}


// �O���f�[�V����
function grady(m){
	var d = document;
	var df = d.forms.palepale;
	var n = 2;
	if(df.gradc){
		for(var j=0; j<df.gradc.length; j++){
			if(df.gradc[j].checked == true){ n = Number(df.gradc[j].value);  break; }
		}
	}
	var cs = String(d.paintbbs.getColors()).split('\n');
	var gs = new Array(1,13);
	if(n==3){ gs = new Array(1,7,13); }
	else if(n==4){ gs = new Array(1,5,9,13); }
	poncheck();
	cs[1] = cs[0];

	// 2�`4�F
	for (var i=0; i<gs.length-1; i++){
		var p=gs[i]; var q=gs[(i+1)];
		var rgbp = to10rgb(cs[p]);	//��RGB
		var rgbq = to10rgb(cs[q]);	//��RGB2
		// HSB
		var hsvp = new Array();
		var hsvq = new Array();
		if(m==1 || m==-1){
			hsvp = RGBtoHSB(rgbp[0],rgbp[1],rgbp[2]);	//��HSB
			hsvq = RGBtoHSB(rgbq[0],rgbq[1],rgbq[2]);	//��HSB
		}
		// �p���b�g�̐F
		for (var k=p+1; k<q; k++){
			var rgb = new Array();
			// HSB
			if(m==1 || m==-1){
				var hsv = new Array();
				for (var j=0; j<=2; j++){ // RGB
					var sa = (hsvp[j]-hsvq[j])/(q-p);
					if(j==0){	// H
						if(m*hsvp[j]>m*hsvq[j]){ sa = Math.abs(sa) - 255/(q-p); }
						hsv[0] = hsvp[0] + m*Math.abs(sa)*(k-p);
						if(hsv[0]<0){ hsv[0]+=255; }else if(hsv[0]>255){ hsv[0]-=255; }
					}else{	// S,B
						hsv[j] = hsvp[j] - sa*(k-p);
						if(hsv[j]<0){ hsv[j]=0; }else if(hsv[j]>255){ hsv[j]=255; }
					}
				}
				rgb = HSBtoRGB(hsv[0],hsv[1],hsv[2]);	//��HSB
				for (var j=0; j<=2; j++){ rgb[j] = Math.round(rgb[j]); }
			// RGB
			}else{
				for (var j=0; j<=2; j++){ // RGB
					var sa = (rgbp[j]-rgbq[j])/(q-p);
					rgb[j] = Math.round(rgbp[j] - sa*(k-p));
					if(rgb[j]<0){ rgb[j]=0; }else if(rgb[j]>255){ rgb[j]=255; }	//����
				}
			}
			cs[k] = '#'+format16(rgb[0])+format16(rgb[1])+format16(rgb[2]);
		}
	}
	cs[0]=cs[1]; cs[1]='#ffffff';
	checkout();
	d.paintbbs.setColors(String(cs.join('\n')));
}


// -------------------------------------------------------------------------
// �p���b�g�̃T���v���J���[
function csamp(p,pz,lst){
	var ss='';
	var ps = pz.split('\n');
	var slong = sams.length;
	var psy = check_h_sb(lst);  if(psy==1){ slong = samx.length; }
	// color-sample
	for (var i=0; i<slong; i++){
	// color-title
		var k,cl='',rgb='',hsv='',ctl='';
		if(psy==1){ k=samx[i]; }else{ k=sams[i]; }
		if(ps[k]){
			rgb = to10rgb(ps[k]);	//��RGB
			hsv = RGBtoHSB(rgb[0],rgb[1],rgb[2]);	//��HSB
			for (var j=0; j<=2; j++){ hsv[j] = Math.round(hsv[j]); }
			ctl  = 'HSB: '+hsv[0]+','+hsv[1]+','+hsv[2]+'\n';
		    ctl += 'RGB: '+rgb[0]+','+rgb[1]+','+rgb[2]+'\nRGB16: '+ps[k];
		}
		if(selfv[2]) cl=selfv[2].replace(/\$FONT/i,ps[k]);
		if(selfv[1]) ss += '<font id="font_'+p+'_'+k+'" '+cl+' title="'+ctl+'">'+selfv[1]+'</font>';
	}
	return ss;
}


// �p���b�g�̃��X�g
function palette_list(lst){
	var d = document;
	var ds = '';
	for (var p=0; p<pals.length; p++){
		if(!pals[p]){ continue; }
		var samw = csamp(p,pals[p],lst); //�T���v��

		// element
		if(selfv[4]) ds+=inr+'name="rad" value="'+p+'" onclick="rady('+p+')" '+selfv[4]+samw+'\n';
//		ds+='<font color="'+ps[0]+'" id="font_'+p+'" title="'+ctl+'">'+samw+'</font>';
		if(selfv[5]) ds+=inp+'onclick="onplus('+p+',0)" '+selfv[5];
		if(selfv[6]) ds+=inp+'onclick="onplus('+p+',1)" '+selfv[6];
		if(selfv[7]) ds+=inp+'onclick="onplus('+p+',2)" '+selfv[7];
		if(selfv[8]) ds+=inp+'onclick="savy('+p+')" '+selfv[8];
		if(selfv[9]) ds+=inp+'onclick="defy('+p+')" '+selfv[9];
	}
	return ds;
}


// �`�F�b�N������A�t�H���g�J���[�̃T���v����ύX
function checkin(p,not){
	qno=''; qmo=''; qon=0;
	if(!pals[p]){ return; }
	var d = document;
	// font-color
	var ps = pals[p].split('\n');
	var slong = sams.length;
	var psy = check_h_sb();  if(psy==1){ slong = samx.length; }
	// color-sample
	for (var i=0; i<slong; i++){
	// color-title
		var k,rgb='',hsv='',ctl='';
		if(psy==1){ k=samx[i]; }else{ k=sams[i]; }
		if(ps[k]){
			rgb = to10rgb(ps[k]);	//��RGB
			hsv = RGBtoHSB(rgb[0],rgb[1],rgb[2]);	//��HSB
			for (var j=0; j<=2; j++){ hsv[j] = Math.round(hsv[j]); }
			ctl  = 'HSB: '+hsv[0]+','+hsv[1]+','+hsv[2]+'\n';
		    ctl += 'RGB: '+rgb[0]+','+rgb[1]+','+rgb[2]+'\nRGB16: '+ps[k];
		}
		// replace
		var ds;
		if(brwz==1){ ds = d.all('font_'+p+'_'+k); }
		else if(brwz==2){ ds = d.getElementById('font_'+p+'_'+k); }
		if(ds){
			if(ds.style.background){ ds.style.background = ps[k]; }
			if(ds.style.backgroundColor){ ds.style.backgroundColor = ps[k]; }
			if(ds.style.color){ ds.style.color = ps[k]; }
			if(ds.color){ ds.color = ps[k]; }
		}
	}

	// check
	if(not!=1){
		var df = d.forms.palepale;
		for(var j=0; j<df.rad.length; j++){
			if(df.rad[j].value == p){
				df.rad[j].checked = true;  break; }
		}
	}
}


// check���O��
function checkout(q){
	pon=0; pno='';
	if(q!=1){ qno=''; qmo=''; qon=0; }
	var df = document.forms.palepale;
	for(var j=0; j<df.rad.length; j++){
		if(df.rad[j].checked == true){
			 df.rad[j].checked = false;  break; }
	}
}


// �ȑO�̃p���b�g�������ۑ�
function poncheck(not){
	var d = document;
	var df = document.forms.palepale;
	if(df.autosave&&df.autosave.checked==false){ return; }
	else if(pon==1){
		var pget = String(d.paintbbs.getColors());
		if(pals[pno] != pget){
			pals[pno] = pget;
			checkin(pno,1);
			if(not!=1){ pcookset(1); }
		}
	}
}


// �p���b�g���Z�[�u
function savy(p){
	var d = document;
	pals[p] = String(d.paintbbs.getColors());
	checkin(p);
	pcookset(1);
	pon=1; pno=p;
}


// �p���b�g���f�t�H���g��
function defy(p){
	checkout();
	var q = pdefs[p];
	var df = document.forms.palepale;
	if(check_h_sb()==1){ q = pdefx[p]; }
	if(q){
		pals[p] = q;
		rady(p,2);
		checkin(p);
	}else{ minsy(p); }
}


// �p���b�g�ǉ�
function plusy(){
	var d = document;
	if(brwz==1 || brwz==2){
		var p=pals.length;
		var pz = String(d.paintbbs.getColors());
		if(pz){ pals[p] = pz; }
		else{
			pals[p] = '#'+Number(d.paintbbs.getInfo().m.iColor).toString(16);
			rady(p,1);
		}
	}
	if(brwz==1 && d.all('palelist').innerHTML){
		d.all('palelist').innerHTML = palette_list();
		checkin(p);
	}else if(brwz==2 && d.getElementById('palelist').innerHTML){
		d.getElementById('palelist').innerHTML = palette_list();
		checkin(p);
	}
}


// �p���b�g�폜
function minsy(p){
	var d = document;
	var df = d.forms.palepale;
	if(!p&&p!=0){
		for(var j=0; j<=df.rad.length; j++){
			if(df.rad[j] && df.rad[j].checked==true){p=Number(df.rad[j].value); break; }
		}
	}
	if((!p&&p!=0)||p<0){ return; }
	pals[p] = '';
	var plong = pdefs.length;
	if(check_h_sb()==1){ plong = pdefx.length; }
	if(p>=plong){
		var k=0;
		var pds = new Array(); pds = pals;
		pals = new Array(); 
		for(var j=0; j<pds.length; j++){
			if(p!=j && pds[j]){ pals[k] = pds[j]; k++; }
		}
	}

	if(brwz==1 && d.all('palelist').innerHTML){
		d.all('palelist').innerHTML = palette_list();
	}else if(brwz==2 && d.getElementById('palelist').innerHTML){
		d.getElementById('palelist').innerHTML = palette_list();
	}
	checkout();
}


// �p���b�g�f�t�H���g
function def_list(){
	var okd = confirm("�S�̂̃p���b�g���f�t�H���g�ɖ߂��܂��B\n��낵���ł����H");
	if(!okd){ return; }
	var d = document;
	var df = d.forms.palepale;
	pals = new Array();
	var psy = 0;
	var plong = pdefs.length;
	if(check_h_sb()==1){ psy=1;  plong = pdefx.length; }
	for (var p=0; p<plong; p++){
		if(psy==1){ pals[p]=pdefx[p]; }else{ pals[p]=pdefs[p]; }
	}
	for (var p=0; p<pals.length; p++){ if(pals[p]){ rady(p,1); } }

	if(brwz==1 && d.all('palelist').innerHTML){
		d.all('palelist').innerHTML = palette_list();
	}else if(brwz==2 && d.getElementById('palelist').innerHTML){
		d.getElementById('palelist').innerHTML = palette_list();
	}else{
		for (var p=0; p<pals.length; p++){
			if(pals[p]){ checkin(p,1); }
		}
	}
}


// �f�t�H���g h_sb �̃t�H�[���̃`�F�b�N. H���Ƀ`�F�b�N�����Ă�Ȃ�1
function check_h_sb(lst){
	var ch = 0;
	var df = document.forms.palepale;
	if(lst!=1 && df && df.h_sb){
		for (var i=0; i<df.h_sb.length; i++){
			if(df.h_sb[i].value==1 && df.h_sb[i].checked==true){ ch=1; break; }
		}
	}else{ ch=psx; }
	return ch;
}


// �p���b�g�f�[�^ �A�b�v���[�h
function pupload(){
	var d = document;
	var df = d.forms.palepale;
	var qs = new Array();
	var palx='';
	if(df.palz){ palx = df.palz.value; }
	if(!palx){ return; }
	pals = new Array();
	if(eval(palx)){}
	else{
		var px = palx.split(/\(|\)/);
		var ps = px[1].split(',');
		for (var p=0; p<ps.length; p++){
			var q=ps[p].replace(/[^0-9a-fA-F]/g,'');  pals[p] = q;
		}
	}

	for (var p=0; p<pals.length; p++){ if(pals[p]){ rady(p,1); } }

	if(brwz==1 && d.all('palelist').innerHTML){
		d.all('palelist').innerHTML = palette_list();
	}else if(brwz==2 && d.getElementById('palelist').innerHTML){
		d.getElementById('palelist').innerHTML = palette_list();
	}else{
		for (var p=0; p<pals.length; p++){
			if(pals[p]){ checkin(p,1); }
		}
	}
}


// �p���b�g�f�[�^ �_�E�����[�h
function pdownload(){
	var d = document;
	var df = d.forms.palepale;
	var qs = new Array();
	for (var p=0; p<pals.length; p++){
		qs[p] = "\'"+pals[p].replace(/\n/g,'\\n')+"\'";
	}
	var palx = 'pals = new Array(\n' + qs.join('\,\n') + '\n);';
	if(df.palz){ df.palz.value = palx; }
}


// �S�̂̃p���b�g�����N�b�L�[�ɃZ�[�u
function pcookset(o){
	var df = document.forms.palepale;
	if(o&&df.autosave&&df.autosave.checked==false){ return; }
	var exp=new Date();
	exp.setTime(exp.getTime()+1000*86400*60);
	var cs = new Array();
	for(var i=0; i<pals.length; i++){
		cs[i] = escape(pals[i].replace(/\n/g,'_'));
	}
	var cooki = '';
	if(df.num){ cooki += df.num.value; }
	cooki += '_'+check_h_sb()+'_%00';
	cooki += cs.join('%00');
	document.cookie = cname + cooki + "; expires=" + exp.toGMTString();
}


// �S�̂̃p���b�g�����N�b�L�[���烍�[�h
function pcookget(){
	var cooks = document.cookie.split("; ");
	var cooki = '';
	for (var i=0; i<cooks.length; i++){
		if (cooks[i].substr(0,cname.length) == cname){
			cooki = cooks[i].substr(cname.length,cooks[i].length);
			break;
		}
	}
	if(cooki){
		var cs = cooki.split('%00');
		pals = new Array();
		for(var i=0; i<cs.length-1; i++){
			pals[i] = unescape(cs[(i+1)]).replace(/\_/g,"\n");
		}
		if(cs[0]){
			var ps = cs[0].split('_');
			if(ps[0]){ pnum = ps[0]; }
			if(ps[1]){ psx = ps[1]; }else if(!ps[1]&&ps[1]==0){ psx=0; }
		}
	}
}


// �������鐔�𑝂₵���茸�炵����
function num_plus(n){
	var df = document.forms.palepale;
	var m = Number(df.num.value); var l=n;
	n *= Math.abs(Math.round(m/10))+1;  if(n==0){ n=l; }
	df.num.value = m+n;
}


// �g�[���Z���N�g�̒l���}
function tone_plus(n){
	var df = document.forms.palepale;
	var m = Number(df.tone.value);
	if(m>0){ n = Math.floor(m/10 + n)*10; }
	if(n<0){ n=0; }else if(n<5){ n=5; }else if(n>100){ n=100; }
	df.tone.value = n;
	tone_sel(n);
}


// �g�[���Z���N�g
function tone_sel(t){
	var dp=document.paintbbs;
	t = Number(t);
	if(t==0){ dp.getInfo().m.iTT = 0; }
	else{ dp.getInfo().m.iTT = Math.floor(t/10)+1; }
}


// -------------------------------------------------------------------------
// document.write
function palette_selfy(){
	var d = document;
	var df = document.forms.palepale;
	var pzs=palette_selfy.arguments;	//�p���b�g�w�肪�������Ƃ�

	// browzer
	if(brwz!=1 && brwz!=2){ return; }

	// �p���b�g�ƃp���b�g�N�b�L�[
	var plong = pdefs.length;
	if(psx==1){ plong = pdefx.length; }
	for (var p=0; p<plong; p++){
		if(psx==1){ pals[p]=pdefx[p]; }else{ pals[p]=pdefs[p]; }
		if(pzs && pzs.length>=1){	var ok=0;	//�H
			for (var q=0; q<pzs.length; q++){ if(p==pzs[q]){ ok=1; break; } }
			if(ok!=1){ pals[p]=''; }
		}
	}
	pcookget();	// cookie-get
	psx_ch[psx] = 'checked ';	
	for (var p=0; p<pals.length; p++){ if(pals[p]){ rady(p,1,1); } }

	// basic
	d.write(selfytag[0]);
	if(selfv[3]) d.write(inr+'name="rad" value="-1" onclick="rady()" '+selfv[3]);
	if(pbase) d.write(csamp(-1,pbase,1));

	// +-���鐔
	if(selfv[0]){
		d.write('\n<small>&nbsp;</small>+-');
		d.write('<input type="text" name="num" value="'+pnum+'" '+selfv[0]);
		d.write(inp+'value="+" onclick="num_plus(1)">');
		d.write(inp+'value="-" onclick="num_plus(-1)">\n');
	}
	// �p���b�g���X�g
	if(pdefs||pdefx) d.write('<div id="palelist">\n'+palette_list(1)+'</div>\n');

	// �S�̂� HSB�ARGB +-
	if(selfytag[1]) d.write(selfytag[1]);
	if(selfv[10]) d.write(inp+'onclick="alplus(0,1)" ' +selfv[10]);
	if(selfv[12]) d.write(inp+'onclick="alplus(1,1)" ' +selfv[12]);
	if(selfv[14]) d.write(inp+'onclick="alplus(2,1)" ' +selfv[14]);
	if(selfv[16]) d.write(inp+'onclick="alrgb(1)" '    +selfv[16]);
	if(selfv[11]) d.write(inp+'onclick="alplus(0,-1)" '+selfv[11]);
	if(selfv[13]) d.write(inp+'onclick="alplus(1,-1)" '+selfv[13]);
	if(selfv[15]) d.write(inp+'onclick="alplus(2,-1)" '+selfv[15]);
	if(selfv[17]) d.write(inp+'onclick="alrgb(-1)" '   +selfv[17]);

	// �g�[���Z���N�g
	if(selfv[0]){
		d.write('Tone <select name="tone" onchange="tone_sel(this.value)">');
		for (var i=0; i<=100; i+=5){
			d.write('<option value="'+i+'">'+i+'%</option>\n'); if(i>=10){i+=5;}
		}
		d.write('</select>');
		d.write(inp+'value="+" onclick="tone_plus(1)">');
		d.write(inp+'value="-" onclick="tone_plus(-1)">\n');
	}

	// GRADATION
	if(selfytag[2]) d.write(selfytag[2]);
	if(selfv[18]) d.write(inr+'name="gradc" value="2" '+selfv[18]);	//18
	if(selfv[19]) d.write(inr+'name="gradc" value="3" '+selfv[19]);	//19
	if(selfv[20]) d.write(inr+'name="gradc" value="4" '+selfv[20]);	//20
	if(selfv[21]) d.write(inp+'onclick="grady(0)" '    +selfv[21]);	//21
	if(selfv[22]) d.write(inp+'onclick="grady(1)" '    +selfv[22]);	//22
	if(selfv[23]) d.write(inp+'onclick="grady(-1)" '   +selfv[23]);	//23

	// �ǉ��E�폜
	if(selfytag[3]) d.write(selfytag[3]);
	if(selfv[24]) d.write(inp+'onclick="plusy()" '     +selfv[24]);	//24
	if(selfv[25]) d.write(inp+'onclick="minsy()" '     +selfv[25]);	//25

	// �Z�[�u�E�I�[�g�Z�[�u
	if(selfytag[4]) d.write(selfytag[4]);
	if(selfv[26]) d.write('<input type="checkbox" name="autosave" value="1" '+selfv[26]);	//26
	if(selfv[27]) d.write(inp+'onclick="pcookset()" '  +selfv[27]);	//27

	// �f�t�H���g
	if(selfytag[5]) d.write(selfytag[5]);
	if(selfv[28]) d.write(inr+'name="h_sb" value="1" ' +psx_ch[1]+selfv[28]);	//28
	if(selfv[29]) d.write(inr+'name="h_sb" value="0" ' +psx_ch[0]+selfv[29]);	//29
	if(selfv[30]) d.write(inp+'onclick="def_list()" '  +selfv[30]);	//30

	// UPLOAD / DOWNLOAD
	if(selfytag[6]) d.write(selfytag[6]);
	if(selfv[31]) d.write('<input type="text" name="palz" '+selfv[31]);	//31
	if(selfv[32]) d.write(inp+'onclick="pupload()" '   +selfv[32]);	//32
	if(selfv[33]) d.write(inp+'onclick="pdownload()" ' +selfv[33]);	//33

	// /FORM
	if(selfytag[7]) d.write(selfytag[7]);
}
