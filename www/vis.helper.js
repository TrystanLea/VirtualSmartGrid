var view =
{
  'start':0,
  'end':0,

  'zoomout':function ()
  {
    var time_window = this.end - this.start;
    var middle = this.start + time_window / 2;
    time_window = time_window * 2;
    this.start = middle - (time_window/2);
    this.end = middle + (time_window/2);
  },

  'zoomin':function ()
  {
    var time_window = this.end - this.start;
    var middle = this.start + time_window / 2;
    time_window = time_window * 0.5;
    this.start = middle - (time_window/2);
    this.end = middle + (time_window/2);
  },

  'panright':function ()
  {
    var time_window = this.end - this.start;
    var shiftsize = time_window * 0.2;
    this.start += shiftsize;
    this.end += shiftsize;
  },

  'panleft':function ()
  {
    var time_window = this.end - this.start;
    var shiftsize = time_window * 0.2;
    this.start -= shiftsize;
    this.end -= shiftsize;
  },

  'timewindow':function(time)
  { 
    this.end = (new Date()).getTime()*0.001;	//Get end time
    this.start = this.end-(3600*24*time);	//Get start time
  },
  
  'calc_interval':function(npoints)
  {
      var interval = Math.round(((this.end - this.start)/npoints));
      
      var outinterval = 5;
      if (interval>10) outinterval = 10;
      if (interval>15) outinterval = 15;
      if (interval>20) outinterval = 20;
      if (interval>30) outinterval = 30;
      if (interval>60) outinterval = 60;
      if (interval>120) outinterval = 120;
      if (interval>180) outinterval = 180;
      if (interval>300) outinterval = 300;
      if (interval>600) outinterval = 600;
      if (interval>900) outinterval = 900;
      if (interval>1200) outinterval = 1200;
      if (interval>1800) outinterval = 1800;
      if (interval>3600*1) outinterval = 3600*1;
      if (interval>3600*2) outinterval = 3600*2;
      if (interval>3600*3) outinterval = 3600*3;
      if (interval>3600*4) outinterval = 3600*4;
      if (interval>3600*5) outinterval = 3600*5;
      if (interval>3600*6) outinterval = 3600*6;
      if (interval>3600*12) outinterval = 3600*12;
      if (interval>3600*24) outinterval = 3600*24;
      
      return outinterval;
  }

}
