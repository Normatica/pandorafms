/* x_slide.js compiled from X 4.0 with XC 0.27b. Distributed by GNU LGPL. For copyrights, license, documentation and more visit Cross-Browser.com */
   
  function xSlideTo (e, x, y, uTime)
{
  if (!(e = xGetElementById (e)))
    return;
  if (!e.timeout)
    e.timeout = 25;
  e.xTarget = x;
  e.yTarget = y;
  e.slideTime = uTime;
  e.stop = false;
  e.yA = e.yTarget - xTop (e);
  e.xA = e.xTarget - xLeft (e);
  if (e.slideLinear)
    e.B = 1 / e.slideTime;
  else
    e.B = Math.PI / (2 * e.slideTime);
  e.yD = xTop (e);
  e.xD = xLeft (e);
  var d = new Date ();
  e.C = d.getTime ();
  if (!e.moving)
    _xSlideTo (e);
}

function
_xSlideTo (e)
{
  if (!(e = xGetElementById (e)))
    return;
  var now, s, t, newY, newX;
  now = new Date ();
  t = now.getTime () - e.C;
  if (e.stop)
    {
      e.moving = false;
    }
  else if (t < e.slideTime)
    {
      setTimeout ("_xSlideTo('" + e.id + "')", e.timeout);
      if (e.slideLinear)
	s = e.B * t;
      else
	s = Math.sin (e.B * t);
      newX = Math.round (e.xA * s + e.xD);
      newY = Math.round (e.yA * s + e.yD);
      xMoveTo (e, newX, newY);
      e.moving = true;
    }
  else
    {
      xMoveTo (e, e.xTarget, e.yTarget);
      e.moving = false;
    }
}
