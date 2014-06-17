<?php
/**
 * Created by PhpStorm.
 * User: Adebola
 * Date: 17/06/2014
 * Time: 11:05
 */

namespace Rospdf;

/**
 * Class CpdfExtra
 * @package Rospdf
 * This class will contain some extras that can be used to enhance the cpdf , especially for charting functions
 * It takes a chunk of its code from the TCPDF library by Nicola which is a more mature library
 * @author Adebola
 * NOTICE: This features are mostly experimental and should be used with caution
 */
class CpdfExtra extends Cpdf {

    /**
     * Current document state.
     * @protected
     * NOTE: value was set to 2 because that indicates that the document is open and ready for writing
     */
    protected $state = 2;

    /**
     * Output a string to the document.
     * @param $sStringSpec string string to output.
     * @protected
     */
    function _out($sStringSpec){
        $this->objects[$this->currentContents]['c'].="\n".$sStringSpec;
    }

    /**
     * Append an elliptical arc to the current path.
     * An ellipse is formed from n Bezier curves.
     * @param $xc (float) Abscissa of center point.
     * @param $yc (float) Ordinate of center point.
     * @param $rx (float) Horizontal radius.
     * @param $ry (float) Vertical radius (if ry = 0 then is a circle, see Circle()). Default value: 0.
     * @param $xang: (float) Angle between the X-axis and the major axis of the ellipse. Default value: 0.
     * @param $angs: (float) Angle start of draw line. Default value: 0.
     * @param $angf: (float) Angle finish of draw line. Default value: 360.
     * @param $pie (boolean) if true do not mark the border point (used to draw pie sectors).
     * @param $nc (integer) Number of curves used to draw a 90 degrees portion of ellipse.
     * @param $startpoint (boolean) if true output a starting point.
     * @param $ccw (boolean) if true draws in counter-clockwise.
     * @param $svg (boolean) if true the angles are in svg mode (already calculated).
     * @return array bounding box coordinates (x min, y min, x max, y max)
     * @author Nicola Asuni
     * @protected
     * @since 4.9.019 (2010-04-26)
     */
    protected function _outellipticalarc($xc, $yc, $rx, $ry, $xang=0, $angs=0, $angf=360, $pie=false, $nc=2, $startpoint=true, $ccw=true, $svg=false) {

        if(empty($this->ez['pageHeight']))
            return null;            // this was just placed to serve as a safeguard for the method

        $iPageHeight = $this->ez['pageHeight'];

        if ($nc < 2) {
            $nc = 2;
        }
        $xmin = 2147483647;
        $ymin = 2147483647;
        $xmax = 0;
        $ymax = 0;
        if ($pie) {
            // center of the arc
            $this->_outPoint($xc, $yc);
        }
        $xang = deg2rad((float) $xang);
        $angs = deg2rad((float) $angs);
        $angf = deg2rad((float) $angf);
        if ($svg) {
            $as = $angs;
            $af = $angf;
        } else {
            $as = atan2((sin($angs) / $ry), (cos($angs) / $rx));
            $af = atan2((sin($angf) / $ry), (cos($angf) / $rx));
        }
        if ($as < 0) {
            $as += (2 * M_PI);
        }
        if ($af < 0) {
            $af += (2 * M_PI);
        }
        if ($ccw AND ($as > $af)) {
            // reverse rotation
            $as -= (2 * M_PI);
        } elseif (!$ccw AND ($as < $af)) {
            // reverse rotation
            $af -= (2 * M_PI);
        }
        $total_angle = ($af - $as);
        if ($nc < 2) {
            $nc = 2;
        }
        // total arcs to draw
        $nc *= (2 * abs($total_angle) / M_PI);
        $nc = round($nc) + 1;
        // angle of each arc
        $arcang = ($total_angle / $nc);
        // center point in PDF coordinates
        $x0 = $xc;
        $y0 = ($iPageHeight - $yc);
        // starting angle
        $ang = $as;
        $alpha = sin($arcang) * ((sqrt(4 + (3 * pow(tan(($arcang) / 2), 2))) - 1) / 3);
        $cos_xang = cos($xang);
        $sin_xang = sin($xang);
        $cos_ang = cos($ang);
        $sin_ang = sin($ang);
        // first arc point
        $px1 = $x0 + ($rx * $cos_xang * $cos_ang) - ($ry * $sin_xang * $sin_ang);
        $py1 = $y0 + ($rx * $sin_xang * $cos_ang) + ($ry * $cos_xang * $sin_ang);
        // first Bezier control point
        $qx1 = ($alpha * ((-$rx * $cos_xang * $sin_ang) - ($ry * $sin_xang * $cos_ang)));
        $qy1 = ($alpha * ((-$rx * $sin_xang * $sin_ang) + ($ry * $cos_xang * $cos_ang)));
        if ($pie) {
            // line from center to arc starting point
            $this->_outLine($px1, $iPageHeight - $py1);
        } elseif ($startpoint) {
            // arc starting point
            $this->_outPoint($px1, $iPageHeight - $py1);
        }
        // draw arcs
        for ($i = 1; $i <= $nc; ++$i) {
            // starting angle
            $ang = $as + ($i * $arcang);
            if ($i == $nc) {
                $ang = $af;
            }
            $cos_ang = cos($ang);
            $sin_ang = sin($ang);
            // second arc point
            $px2 = $x0 + ($rx * $cos_xang * $cos_ang) - ($ry * $sin_xang * $sin_ang);
            $py2 = $y0 + ($rx * $sin_xang * $cos_ang) + ($ry * $cos_xang * $sin_ang);
            // second Bezier control point
            $qx2 = ($alpha * ((-$rx * $cos_xang * $sin_ang) - ($ry * $sin_xang * $cos_ang)));
            $qy2 = ($alpha * ((-$rx * $sin_xang * $sin_ang) + ($ry * $cos_xang * $cos_ang)));
            // draw arc
            $cx1 = ($px1 + $qx1);
            $cy1 = ($iPageHeight - ($py1 + $qy1));
            $cx2 = ($px2 - $qx2);
            $cy2 = ($iPageHeight - ($py2 - $qy2));
            $cx3 = $px2;
            $cy3 = ($iPageHeight - $py2);
            $this->_outCurve($cx1, $cy1, $cx2, $cy2, $cx3, $cy3);
            // get bounding box coordinates
            $xmin = min($xmin, $cx1, $cx2, $cx3);
            $ymin = min($ymin, $cy1, $cy2, $cy3);
            $xmax = max($xmax, $cx1, $cx2, $cx3);
            $ymax = max($ymax, $cy1, $cy2, $cy3);
            // move to next point
            $px1 = $px2;
            $py1 = $py2;
            $qx1 = $qx2;
            $qy1 = $qy2;
        }
        if ($pie) {
            $this->_outLine($xc, $yc);
            // get bounding box coordinates
            $xmin = min($xmin, $xc);
            $ymin = min($ymin, $yc);
            $xmax = max($xmax, $xc);
            $ymax = max($ymax, $yc);
        }
        return array($xmin, $ymin, $xmax, $ymax);
    }

    /**
     * Begin a new subpath by moving the current point to coordinates (x, y), omitting any connecting line segment.
     * @param $x (float) Abscissa of point.
     * @param $y (float) Ordinate of point.
     * @protected
     * @since 2.1.000 (2008-01-08)
     */
    protected function _outPoint($x, $y) {
        if ($this->state == 2) {
            $this->_out(sprintf('%F %F m', ($x), (( $y))));
        }
    }

    /**
     * Append a straight line segment from the current point to the point (x, y).
     * The new current point shall be (x, y).
     * @param $x (float) Abscissa of end point.
     * @param $y (float) Ordinate of end point.
     * @protected
     * @since 2.1.000 (2008-01-08)
     */
    protected function _outLine($x, $y) {
        if ($this->state == 2) {
            $this->_out(sprintf('%F %F l', ($x), (( $y))));
        }
    }

    /**
     * Append a rectangle to the current path as a complete subpath, with lower-left corner (x, y) and dimensions widthand height in user space.
     * @param $x (float) Abscissa of upper-left corner.
     * @param $y (float) Ordinate of upper-left corner.
     * @param $w (float) Width.
     * @param $h (float) Height.
     * @param $op (string) options
     * @protected
     * @since 2.1.000 (2008-01-08)
     */
    protected function _outRect($x, $y, $w, $h, $op) {
        if ($this->state == 2) {
            $this->_out(sprintf('%F %F %F %F re %s', ($x), (( $y) ), ($w), (-$h ), $op));
        }
    }

    /**
     * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using (x1, y1) and (x2, y2) as the Bezier control points.
     * The new current point shall be (x3, y3).
     * @param $x1 (float) Abscissa of control point 1.
     * @param $y1 (float) Ordinate of control point 1.
     * @param $x2 (float) Abscissa of control point 2.
     * @param $y2 (float) Ordinate of control point 2.
     * @param $x3 (float) Abscissa of end point.
     * @param $y3 (float) Ordinate of end point.
     * @protected
     * @since 2.1.000 (2008-01-08)
     */
    protected function _outCurve($x1, $y1, $x2, $y2, $x3, $y3) {
        if ($this->state == 2) {
            $this->_out(sprintf('%F %F %F %F %F %F c', ($x1), (( $y1) ), ($x2), (( $y2) ), ($x3), (( $y3))));
        }
    }

    /**
     * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using the current point and (x2, y2) as the Bezier control points.
     * The new current point shall be (x3, y3).
     * @param $x2 (float) Abscissa of control point 2.
     * @param $y2 (float) Ordinate of control point 2.
     * @param $x3 (float) Abscissa of end point.
     * @param $y3 (float) Ordinate of end point.
     * @protected
     * @since 4.9.019 (2010-04-26)
     */
    protected function _outCurveV($x2, $y2, $x3, $y3) {
        if ($this->state == 2) {
            $this->_out(sprintf('%F %F %F %F v', ($x2), (( $y2)), ($x3), (( $y3))));
        }
    }

    /**
     * Append a cubic Bezier curve to the current path. The curve shall extend from the current point to the point (x3, y3), using (x1, y1) and (x3, y3) as the Bezier control points.
     * The new current point shall be (x3, y3).
     * @param $x1 (float) Abscissa of control point 1.
     * @param $y1 (float) Ordinate of control point 1.
     * @param $x3 (float) Abscissa of end point.
     * @param $y3 (float) Ordinate of end point.
     * @protected
     * @since 2.1.000 (2008-01-08)
     */
    protected function _outCurveY($x1, $y1, $x3, $y3) {
        if ($this->state == 2) {
            $this->_out(sprintf('%F %F %F %F y', ($x1), (( $y1)), ($x3), (( $y3))));
        }
    }


    /**
     * Draws a rounded rectangle.
     * @param $x (float) Abscissa of upper-left corner.
     * @param $y (float) Ordinate of upper-left corner.
     * @param $w (float) Width.
     * @param $h (float) Height.
     * @param $r (float) the radius of the circle used to round off the corners of the rectangle.
     * @param $round_corner (string) Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top right, bottom right, bottom left and top left. Default value: all rounded corner ("1111").
     * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
     * @param $border_style (array) Border style of rectangle. Array like for SetLineStyle(). Default value: default line style (empty array).
     * @param $fill_color (array) Fill color. Format: array(GREY) or array(R,G,B) or array(C,M,Y,K) or array(C,M,Y,K,SpotColorName). Default value: default color (empty array).
     * @public
     * @since 2.1.000 (2008-01-08)
     */
    public function RoundedRect($x, $y, $w, $h, $r, $round_corner='1111', $style='', $border_style=array(), $fill_color=array()) {
        $this->RoundedRectXY($x, $y, $w, $h, $r, $r, $round_corner, $style, $border_style, $fill_color);
    }

    /**
     * Draws a rounded rectangle.
     * @param $x (float) Abscissa of upper-left corner.
     * @param $y (float) Ordinate of upper-left corner.
     * @param $w (float) Width.
     * @param $h (float) Height.
     * @param $rx (float) the x-axis radius of the ellipse used to round off the corners of the rectangle.
     * @param $ry (float) the y-axis radius of the ellipse used to round off the corners of the rectangle.
     * @param $round_corner (string) Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top right, bottom right, bottom left and top left. Default value: all rounded corner ("1111").
     * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
     * @public
     * @since 4.9.019 (2010-04-22)
     *
     * NOTE: You  should set the Border Style and Fill Color From outside this method
     */
    public function RoundedRectXY($x, $y, $w, $h, $rx, $ry, $round_corner='1111', $style='F') {
		if ($this->state != 2) {
			return;
		}

        if (($round_corner == '0000') OR (($rx == $ry) AND ($rx == 0))) {
            // Not rounded
            $this->filledRectangle($x, $y, $w,$h);
            return;
        }

        $op = self::getPathPaintOperator($style);


        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_outPoint($x + $rx, $y);
        $xc = $x + $w - $rx;
        $yc = $y + $ry;
        $this->_outLine($xc, $y);
        if ($round_corner[0]) {
            $this->_outCurve($xc + ($rx * $MyArc), $yc - $ry, $xc + $rx, $yc - ($ry * $MyArc), $xc + $rx, $yc);
        } else {
            $this->_outLine($x + $w, $y);
        }
        $xc = $x + $w - $rx;
        $yc = $y + $h - $ry;
        $this->_outLine($x + $w, $yc);
        if ($round_corner[1]) {
            $this->_outCurve($xc + $rx, $yc + ($ry * $MyArc), $xc + ($rx * $MyArc), $yc + $ry, $xc, $yc + $ry);
        } else {
            $this->_outLine($x + $w, $y + $h);
        }
        $xc = $x + $rx;
        $yc = $y + $h - $ry;
        $this->_outLine($xc, $y + $h);
        if ($round_corner[2]) {
            $this->_outCurve($xc - ($rx * $MyArc), $yc + $ry, $xc - $rx, $yc + ($ry * $MyArc), $xc - $rx, $yc);
        } else {
            $this->_outLine($x, $y + $h);
        }
        $xc = $x + $rx;
        $yc = $y + $ry;
        $this->_outLine($x, $yc);
        if ($round_corner[3]) {
            $this->_outCurve($xc - $rx, $yc - ($ry * $MyArc), $xc - ($rx * $MyArc), $yc - $ry, $xc, $yc - $ry);
        } else {
            $this->_outLine($x, $y);
            $this->_outLine($x + $rx, $y);
        }
        $this->_out($op);
    }


    /**
     * @param $style
     * @param string $default
     * @return string
     */
    public static function getPathPaintOperator($style, $default='S') {
        $op = '';
        switch($style) {
            case 'S':
            case 'D': {
                $op = 'S';
                break;
            }
            case 's':
            case 'd': {
                $op = 's';
                break;
            }
            case 'f':
            case 'F': {
                $op = 'f';
                break;
            }
            case 'f*':
            case 'F*': {
                $op = 'f*';
                break;
            }
            case 'B':
            case 'FD':
            case 'DF': {
                $op = 'B';
                break;
            }
            case 'B*':
            case 'F*D':
            case 'DF*': {
                $op = 'B*';
                break;
            }
            case 'b':
            case 'fd':
            case 'df': {
                $op = 'b';
                break;
            }
            case 'b*':
            case 'f*d':
            case 'df*': {
                $op = 'b*';
                break;
            }
            case 'CNZ': {
                $op = 'W n';
                break;
            }
            case 'CEO': {
                $op = 'W* n';
                break;
            }
            case 'n': {
                $op = 'n';
                break;
            }
            default: {
            if (!empty($default)) {
                $op = self::getPathPaintOperator($default, '');
            } else {
                $op = '';
            }
            }
        }
        return $op;
    }


    /**
     * Draw the sector of a circle.
     * It can be used for instance to render pie charts.
     * @param $xc (float) abscissa of the center.
     * @param $yc (float) ordinate of the center.
     * @param $r (float) radius.
     * @param $a (float) start angle (in degrees).
     * @param $b (float) end angle (in degrees).
     * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
     * @param $cw: (float) indicates whether to go clockwise (default: true).
     * @param $o: (float) origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock). Default: 90.
     * @author Maxime Delorme, Nicola Asuni
     * @since 3.1.000 (2008-06-09)
     * @public
     */
    public function PieSector($xc, $yc, $r, $a, $b, $style='FD', $cw=true, $o=90) {
        $this->PieSectorXY($xc, $yc, $r, $r, $a, $b, $style, $cw, $o);
    }

    /**
     * Draw the sector of an ellipse.
     * It can be used for instance to render pie charts.
     * @param $xc (float) abscissa of the center.
     * @param $yc (float) ordinate of the center.
     * @param $rx (float) the x-axis radius.
     * @param $ry (float) the y-axis radius.
     * @param $a (float) start angle (in degrees).
     * @param $b (float) end angle (in degrees).
     * @param $style (string) Style of rendering. See the getPathPaintOperator() function for more information.
     * @param $cw: (float) indicates whether to go clockwise.
     * @param $o: (float) origin of angles (0 for 3 o'clock, 90 for noon, 180 for 9 o'clock, 270 for 6 o'clock).
     * @param $nc (integer) Number of curves used to draw a 90 degrees portion of arc.
     * @author Maxime Delorme, Nicola Asuni
     * @since 3.1.000 (2008-06-09)
     * @public
     */
    public function PieSectorXY($xc, $yc, $rx, $ry, $a, $b, $style='FD', $cw=false, $o=0, $nc=2) {
        if ($this->state != 2) {
            return;
        }
        $iPageWidth = $this->ez['pageWidth'];
        if ($this->rtl) {
            //$xc = ($this->w - $xc);
            $xc = ($iPageWidth - $xc);
        }
        $op = self::getPathPaintOperator($style);
        if ($op == 'f') {
            $line_style = array();
        }
        if ($cw) {
            $d = $b;
            $b = (360 - $a + $o);
            $a = (360 - $d + $o);
        } else {
            $b += $o;
            $a += $o;
        }
        $this->_outellipticalarc($xc, $yc, $rx, $ry, 0, $a, $b, true, $nc);
        $this->_out($op);
    }

} 