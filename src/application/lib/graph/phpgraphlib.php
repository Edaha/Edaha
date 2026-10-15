<?php

// /////////////////////////////////////////////////////////
// PHPGraphLib -  PHP Graphing Library v2.02
// Author: Elliott Brueggeman
// PHP v4.04 + compatible
// Please visit www.ebrueggeman.com for usage policy
// and full documentation + examples
// /////////////////////////////////////////////////////////
class PHPGraphLib
{
    // ---------------USER CHANGEABLE DEFAULTS----------------/
    public $height = 300;
    public $width = 400;
    public $data_max_allowable = 9999999999999999;
    public $data_min_allowable = -9999999999999999;
    public $data_set_count = 0;
    // SET TO ACTUAL FONT HEIGHTS AND WIDTHS USED
    public $title_char_width = 6;
    public $title_char_height = 12;
    public $text_width = 6;
    public $text_height = 12;
    public $data_value_text_width = 6;
    public $data_value_text_height = 12;
    // PADDING BETWEEN AXIS AND VALUE DISPLAYED
    public $axis_value_padding = 5;
    // SPACE B/T TOP OF BAR OR CENTER OF POINT AND DATA VALUE
    public $data_value_padding = 5;
    // DEFAULT MARGIN % OF WIDTH / HEIGHT
    public $x_axis_default_percent = 12;
    public $y_axis_default_percent = 8;
    // DATA POINT DIAMETER IN PX
    public $data_point_width = 6;
    // USER CHANGEABLE DEFAULT BOOLEANS (SHOW ELEMENT BY DEFAULT?)
    public $bool_bar_outline = true;
    public $bool_x_axis = true;
    public $bool_y_axis = true;
    public $bool_x_axis_values = true;
    public $bool_y_axis_values = true;
    public $bool_grid = true;
    public $bool_line = false;
    public $bool_data_values = false;
    public $bool_x_axis_values_vert = true;
    public $bool_data_points = false;
    public $bool_title_left = false;
    public $bool_title_right = false;
    public $bool_title_center = true;
    // ----------INTERNAL VARIABLES (DO NOT CHANGE)------------/
    public $image;
    public $error;
    public $bool_x_axis_setup = false;
    public $bool_y_axis_setup = false;
    public $bool_data = false;
    public $bool_bars_generate = true;
    public $bool_bars = true;
    public $bool_background = false;
    public $bool_title = false;
    public $bool_all_negative = false;
    public $bool_all_positive = false;
    public $bool_gradient = false;
    public $bool_user_data_range = false;
    public $bool_gradient_colors_found; // INIT AS ARRAY
    // COLOR VARS
    public $background_color;
    public $grid_color;
    public $bar_color;
    public $outline_color;
    public $x_axis_text_color;
    public $y_axis_text_color;
    public $title_color;
    public $x_axis_color;
    public $y_axis_color;
    public $data_point_color;
    public $data_value_color;
    public $line_color;
    public $line_color_default;
    public $goal_line_color;
    // GRADIENT COLORS STORED AS ARRAYS, NOT ALLOCATED COLOR
    public $gradient_color_1;
    public $gradient_color_2;
    public $gradient_color_array;
    public $gradient_max = 200;
    public $gradient_handicap;
    // DATA VARS
    public $data_array;
    public $data_count;
    public $data_min;
    public $data_max;
    // BAR VARS / SCALE
    public $bar_spaces;
    public $bar_width;
    public $space_width;
    public $unit_scale;
    public $goal_line_array;
    // TEXT / FONT
    public $title_x;
    public $title_y;
    // AXIS POINTS
    public $x_axis_x1;
    public $x_axis_y1;
    public $x_axis_x2;
    public $x_axis_y2;
    public $y_axis_x1;
    public $y_axis_y1;
    public $y_axis_x2;
    public $y_axis_y2;
    public $x_axis_margin; // AKA BOTTOM MARGIN
    public $y_axis_margin; // AKA LEFT MARGIN
    public $data_range_max;
    public $data_range_min;
    public $top_margin = 0;
    public $right_margin = 0;
    public $range_divisor_factor = 25; // CONTROLS AUTO-ADJUSTING GRID INTERVAL
    public $data_point_array;
    // MULTIPLE DATASET VARIABLES
    public $bool_multi_offset = true;
    public $multi_offset_two = 24; // PERCENT OF BAR WIDTH
    public $multi_offset_three = 15; // PERCENT OF BAR WIDTH
    public $multi_gradient_colors_1;
    public $multi_gradient_colors_2;
    public $multi_bar_colors;
    public $color_darken_factor = 30; // PERCENT DECREASE
    // LEGEND VARIABLES
    public $bool_legend = false;
    public $legend_text_width = 6; // IN PX...
    public $legend_max_chars = 15;
    public $legend_total_chars;
    public $legend_text_height = 12;
    public $legend_padding = 4; // PADDING INSIDE LEGEND BOX
    public $legend_width;
    public $legend_height;
    public $legend_x;
    public $legend_y;
    public $legend_text_color;
    public $legend_outline_color;
    public $legend_swatch_outline_color;
    public $legend_color;
    public $legend_titles = [];

    // --------------------"PUBLIC" CONSTRUCTOR----------------------//
    public function PHPGraphLib($width = '', $height = '')
    {
        if (!empty($width) && !empty($height)) {
            $this->width = $width;
            $this->height = $height;
        }
        $this->initialize();
        $this->allocateColors(); // SETS DEFAULT COLORS
    }

    // ----------------"PRIVATE" MAIN PROGRAM FUNCTIONS ----------------//
    public function initialize()
    {
        // HEADER MUST BE SENT BEFORE ANY HTML OR BLANK SPACE OUTPUT
        header('Content-type: image/png');
        $this->image = @imagecreate($this->width, $this->height)
            or exit('Cannot Initialize new GD image stream - Check your PHP setup');
        $this->data_point_array = [];
        $this->goal_line_array = [];
        $this->multi_bar_colors = [];
        $this->multi_gradient_colors_1 = [];
        $this->multi_gradient_colors_2 = [];
        // THESE VALUES ARE NOW ARRAYS
        $this->gradient_handicap = [];
        $this->bool_gradient_colors_found = [];
        $this->legend_total_chars = [];
        $this->line_color = [];
    }

    public function createGraph() // MAIN CLASS METHOD - CALLED LAST
    {
        // SETUP AXIS IF NOT ALREADY SETUP BY USER
        if ($this->bool_data) {
            if (!$this->bool_x_axis_setup) {
                $this->setupXAxis();
            }
            if (!$this->bool_y_axis_setup) {
                $this->setupYAxis();
            }
            // CALCULATIONS
            $this->calcTopMargin();
            $this->calcRightMargin();
            $this->calcCoords();
            $this->setupData();
            // START CREATING ACTUAL IMAGE ELEMENTS
            if ($this->bool_background) {
                $this->generateBackgound();
            }
            // ALWAYS GEN GRID VALUES, EVEN IF NOT DISPLAYED
            $this->generateGrid();
            if ($this->bool_bars_generate) {
                $this->generateBars();
            }
            if ($this->bool_data_points) {
                $this->generateDataPoints();
            }
            if ($this->bool_legend) {
                $this->generateLegend();
            }
            if ($this->bool_title) {
                $this->generateTitle();
            }
            if ($this->bool_x_axis) {
                $this->generateXAxis();
            }
            if ($this->bool_y_axis) {
                $this->generateYAxis();
            }
        } else {
            $this->error[] = 'No valid data added to graph. Add data with the addData() function.';
        }
        // DISPLAY ERRORS
        $this->displayErrors();
        // OUTPUT TO BROWSER
        imagepng($this->image);
        imagedestroy($this->image);
    }

    public function setupData()
    {
        $this->bar_spaces = ($this->data_count * 2) + 1;
        $unit_width = ($this->width - $this->y_axis_margin - $this->right_margin) / (($this->data_count * 2) + $this->data_count);
        if ($unit_width < 1) {
            // ERROR UNITS TOO SMALL, TOO MANY DATA POINTS OR NOT LARGE ENOUGH GRAPH
            $this->bool_bars_generate = false;
            $this->error[] = 'Graph too small or too many data points.';
        } else {
            // DEFAULT SPACE BETWEEN BARS IS 1/2 THE WIDTH OF THE BAR
            // FIND BAR AND SPACE WIDTHS. BAR = 2 UNITS, SPACE = 1 UNIT
            $this->bar_width = 2 * $unit_width;
            $this->space_width = $unit_width;
            // NOW CALCULATE HEIGHT (SCALE) UNITS
            $availVertSpace = $this->height - $this->x_axis_margin - $this->top_margin;
            if ($availVertSpace < 1) {
                $this->bool_bars_generate = false;
                $this->error[] = 'Graph height not tall enough.';
            // ERROR SCALE UNITS TOO SMALL, X AXIS MARGIN TOO BIG OR GRAPH HEIGHT NOT TALL ENOUGH
            } else {
                if ($this->bool_user_data_range) {
                    if (($this->data_range_max < $this->data_max) || ($this->data_range_min > $this->data_min)) {
                        $this->error[] = 'Data not usable with setRange() function. All data must be within range!';
                    }
                    if ($this->data_min < 0) {
                        $this->error[] = 'Data not usable with setRange() function. All data must be >= 0.';
                    } else {
                        $graphTopScale = $this->data_range_max;
                        $graphBottomScale = $this->data_range_min;
                        $graphScaleRange = $graphTopScale - $graphBottomScale;
                        $this->unit_scale = $availVertSpace / $graphScaleRange;
                        $this->data_max = $this->data_range_max;
                        $this->data_min = $this->data_range_min;
                    }
                } else {
                    // START AT Y VALUE 0 OR DATA MIN, WHICHEVER IS LESS
                    $graphBottomScale = ($this->data_min < 0) ? $this->data_min : 0;
                    $graphTopScale = ($this->data_max < 0) ? 0 : $this->data_max;
                    $graphScaleRange = $graphTopScale - $graphBottomScale;
                    $this->unit_scale = $availVertSpace / $graphScaleRange;
                    // NOW ADJUST X AXIS IN Y VALUE IF NEGATIVE VALUES
                    if ($this->data_min < 0) {
                        $this->x_axis_y1 -= (int) ($this->unit_scale * abs($this->data_min));
                        $this->x_axis_y2 -= (int) ($this->unit_scale * abs($this->data_min));
                    }
                }
                $this->bool_bars_generate = true;
            }
        }
    }

    public function generateBars()
    {
        $this->finalizeColors();
        $barCount = 0;
        $adjustment = 0;
        if ($this->bool_user_data_range) {
            $adjustment = $this->data_min * $this->unit_scale;
        }
        // REVERSE ARRAY TO ORDER DATA SETS IN ORDER OF PRIORITY
        $this->data_array = array_reverse($this->data_array);
        $dataset_offset = 0;
        if ($this->bool_multi_offset) {
            // SET DIFFERENT OFFSETS BASED ON NUMBER OF DATA SETS
            $dataset_offset = ($this->data_set_count > 2)
                ? $this->bar_width * ($this->multi_offset_three / 100) : $this->bar_width * ($this->multi_offset_two / 100);
        }
        foreach ($this->data_array as $data_set_num => $data_set) {
            $lineX2 = null;
            $xStart = $this->y_axis_x1 + $this->space_width / 2;
            foreach ($data_set as $key => $item) {
                $x1 = (int) $xStart + ($dataset_offset * $data_set_num);
                $x2 = (int) ($xStart + $this->bar_width) + ($dataset_offset * $data_set_num);
                $y1 = (int) ($this->x_axis_y1 - ($item * $this->unit_scale) + $adjustment);
                $y2 = (int) $this->x_axis_y1;

                // DRAW BAR
                if ($this->bool_bars) {
                    if ($this->bool_gradient) {
                        // DRAW GRADIENT IF DESIRED
                        $this->drawGradientBar($x1, $y1, $x2, $y2, $this->multi_gradient_colors_1[$data_set_num], $this->multi_gradient_colors_2[$data_set_num], $data_set_num);
                    } else {
                        // IF/ELSE NECESSARY B/C OF BUG IN ARG ORDER OF imagefilledrectangle() FUNCTION
                        if ($y1 < $y2) {
                            imagefilledrectangle($this->image, $x1, $y1, $x2, $y2, $this->multi_bar_colors[$data_set_num]);
                        } else {
                            imagefilledrectangle($this->image, $x1, $y2, $x2, $y1, $this->multi_bar_colors[$data_set_num]);
                        }
                    }
                    // DRAW BAR OUTLINE
                    if ($this->bool_bar_outline) {
                        imagerectangle($this->image, $x1, $y2, $x2, $y1, $this->outline_color);
                    }
                }
                // DRAW LINE
                if ($this->bool_line) {
                    $lineX1 = $x1 + $this->bar_width / 2;
                    $lineY1 = $y1;
                    if (isset($lineX2)) {
                        imageline($this->image, $lineX2, $lineY2, $lineX1, $lineY1, $this->line_color[$data_set_num]);
                        $lineX2 = $lineX1;
                        $lineY2 = $lineY1;
                    } else {
                        $lineX2 = $lineX1;
                        $lineY2 = $lineY1;
                    }
                }
                // DISPLAY DATA POINTS
                if ($this->bool_data_points) {
                    // DONT DRAW DATAPOINTS HERE OR WILL OVERLAP POORLY WITH LINE
                    // INSTEAD COLLECT COORDINATES
                    $pointX = $x1 + $this->bar_width / 2;
                    $this->data_point_array[] = [$pointX, $y1];
                }
                // DISPLAY DATA VALUES
                if ($this->bool_data_values) {
                    $dataX = ($x1 + $this->bar_width / 2) - ((strlen($item) * $this->data_value_text_width) / 2);
                    $dataY = ($item >= 0) ? $y1 - $this->data_value_padding - $this->data_value_text_height : $y1 + $this->data_value_padding;
                    imagestring($this->image, 2, $dataX, $dataY, $item, $this->data_value_color);
                }

                // WRITE X AXIS VALUE
                if ($this->bool_x_axis_values) {
                    if ($data_set_num == $this->data_set_count - 1) {
                        if ($this->bool_x_axis_values_vert) {
                            if ($this->bool_all_negative) {
                                // WE MUST PUT VALUES ABOVE 0 LINE
                                $textVertPos = (int) ($this->y_axis_y2 - $this->axis_value_padding);
                            } else {
                                // MIX OF BOTH POS AND NEG NUMBERS
                                // WRITE VALUE Y AXIS BOTTOM VALUE (WILL BE UNDER BOTTOM OF GRID EVEN IF X AXIS IS FLOATING DUE TO
                                $textVertPos = (int) ($this->y_axis_y1 + strlen($key) * $this->text_width + $this->axis_value_padding);
                            }
                            $textHorizPos = (int) ($xStart + ($this->bar_width / 2) - ($this->text_height / 2));
                            imagestringup($this->image, 2, $textHorizPos, $textVertPos, $key, $this->x_axis_text_color);
                        } else {
                            if ($this->bool_all_negative) {
                                // WE MUST PUT VALUES ABOVE 0 LINE
                                $textVertPos = (int) ($this->y_axis_y2 - $this->text_height - $this->axis_value_padding);
                            } else {
                                // MIX OF BOTH POS AND NEG NUMBERS
                                // WRITE VALUE Y AXIS BOTTOM VALUE (WILL BE UNDER BOTTOM OF GRID EVEN IF X AXIS IS FLOATING DUE TO
                                $textVertPos = (int) ($this->y_axis_y1 + ($this->text_height * 2 / 3) - $this->axis_value_padding);
                            }
                            // HORIZONTAL DATA KEYS
                            $textHorizPos = (int) ($xStart + ($this->bar_width / 2) - ((strlen($key) * $this->text_width) / 2));
                            imagestring($this->image, 2, $textHorizPos, $textVertPos, $key, $this->x_axis_text_color);
                        }
                    }
                }
                $xStart += $this->bar_width + $this->space_width;
            }
        }
    }

    public function finalizeColors()
    {
        if ($this->bool_gradient) {
            $num_set = count($this->multi_gradient_colors_1);
            // LOOP THROUGH SET COLORS AND ADD BACKING COLORS IF NECESSARY
            if ($num_set != $this->data_set_count) {
                $color_darken_decimal = (100 - $this->color_darken_factor) / 100;
                while ($num_set < $this->data_set_count) {
                    $color_ref_1 = $this->multi_gradient_colors_1[$num_set - 1];
                    $color_ref_2 = $this->multi_gradient_colors_2[$num_set - 1];
                    $this->multi_gradient_colors_1[] = [
                        (int) $color_ref_1[0] * $color_darken_decimal,
                        (int) $color_ref_1[1] * $color_darken_decimal,
                        (int) $color_ref_1[2] * $color_darken_decimal];
                    $this->multi_gradient_colors_2[] = [
                        (int) $color_ref_2[0] * $color_darken_decimal,
                        (int) $color_ref_2[1] * $color_darken_decimal,
                        (int) $color_ref_2[2] * $color_darken_decimal];
                    ++$num_set;
                }
            }
            while (count($this->multi_gradient_colors_1) > $this->data_set_count) {
                $temp = array_pop($this->multi_gradient_colors_1);
            }
            while (count($this->multi_gradient_colors_2) > $this->data_set_count) {
                $temp = array_pop($this->multi_gradient_colors_2);
            }
            $this->multi_gradient_colors_1 = array_reverse($this->multi_gradient_colors_1);
            $this->multi_gradient_colors_2 = array_reverse($this->multi_gradient_colors_2);
        } elseif (!$this->bool_gradient) {
            $num_set = count($this->multi_bar_colors);
            if (0 == $num_set) {
                $this->multi_bar_colors[0] = $this->bar_color;
                $num_set = 1;
            }
            // LOOP THROUGH SET COLORS AND ADD BACKING COLORS IF NECESSARY
            while ($num_set < $this->data_set_count) {
                $color_ref = $this->multi_bar_colors[$num_set - 1];
                $color_parts = imagecolorsforindex($this->image, $color_ref);
                $color_darken_decimal = (100 - $this->color_darken_factor) / 100;
                $this->multi_bar_colors[$num_set] = imagecolorallocate(
                    $this->image,
                    (int) $color_parts['red'] * $color_darken_decimal,
                    (int) $color_parts['green'] * $color_darken_decimal,
                    (int) $color_parts['blue'] * $color_darken_decimal
                );
                ++$num_set;
            }
            while (count($this->multi_bar_colors) > $this->data_set_count) {
                $temp = array_pop($this->multi_bar_colors);
            }
            $this->multi_bar_colors = array_reverse($this->multi_bar_colors);
        }
        if ($this->bool_line) {
            if (!$this->bool_bars) {
                $num_set = count($this->line_color);
                if (0 == $num_set) {
                    $this->line_color[0] = $this->line_color_default;
                    $num_set = 1;
                }
                // ONLY DARKEN EACH DATA SET'S LINES WHEN NO BARS PRESENT
                while ($num_set < $this->data_set_count) {
                    $color_ref = $this->line_color[$num_set - 1];
                    $color_parts = imagecolorsforindex($this->image, $color_ref);
                    $color_darken_decimal = (100 - $this->color_darken_factor) / 100;
                    $this->line_color[$num_set] = imagecolorallocate(
                        $this->image,
                        (int) $color_parts['red'] * $color_darken_decimal,
                        (int) $color_parts['green'] * $color_darken_decimal,
                        (int) $color_parts['blue'] * $color_darken_decimal
                    );
                    ++$num_set;
                }
            } else {
                $num_set = count($this->line_color);
                while ($num_set < $this->data_set_count) {
                    $this->line_color[$num_set] = $this->line_color_default;
                    ++$num_set;
                }
            }
            while (count($this->line_color) > $this->data_set_count) {
                $temp = array_pop($this->line_color);
            }
            $this->line_color = array_reverse($this->line_color);
        }
    }

    public function drawGradientBar($x1, $y1, $x2, $y2, $colorArr1, $colorArr2, $data_set_num)
    {
        if (!isset($this->bool_gradient_colors_found[$data_set_num]) || false == $this->bool_gradient_colors_found[$data_set_num]) {
            $this->gradient_handicap[$data_set_num] = 0;
            $numLines = abs($x1 - $x2) + 1;
            while ($numLines * $this->data_set_count > $this->gradient_max) {
                // WE HAVE MORE LINES THAN ALLOWABLE COLORS
                // USE HANDICAP TO RECORD THIS
                $numLines /= 2;
                ++$this->gradient_handicap[$data_set_num];
            }
            $color1R = $colorArr1[0];
            $color1G = $colorArr1[1];
            $color1B = $colorArr1[2];
            $color2R = $colorArr2[0];
            $color2G = $colorArr2[1];
            $color2B = $colorArr2[2];
            $rScale = ($color1R - $color2R) / $numLines;
            $gScale = ($color1G - $color2G) / $numLines;
            $bScale = ($color1B - $color2B) / $numLines;
            $this->allocateGradientColors($color1R, $color1G, $color1B, $rScale, $gScale, $bScale, $numLines, $data_set_num);
        }
        $numLines = abs($x1 - $x2) + 1;
        if ($this->gradient_handicap[$data_set_num] > 0) {
            // IF HANDICAP IS USED, IT WILL ALLOW US TO MOVE THROUGH THE ARRAY MORE SLOWLY, DEPENDING ON THE SET VALUE
            $interval = $this->gradient_handicap[$data_set_num];
            for ($i = 0; $i < $numLines; ++$i) {
                $adjusted_index = ceil($i / pow(2, $interval)) - 1;
                if ($adjusted_index < 0) {
                    $adjusted_index = 0;
                }
                imageline($this->image, $x1 + $i, $y1, $x1 + $i, $y2, $this->gradient_color_array[$data_set_num][$adjusted_index]);
            }
        } else {
            // NORMAL GRADIENTS WITH COLORS < $this->gradient_max
            for ($i = 0; $i < $numLines; ++$i) {
                imageline($this->image, $x1 + $i, $y1, $x1 + $i, $y2, $this->gradient_color_array[$data_set_num][$i]);
            }
        }
    }

    public function generateGrid()
    {
        // DETERMINE HORIZONTAL GRID LINES
        $horizGridArray = [];
        if ($this->bool_user_data_range) {
            $min = $this->data_min;
        } else {
            $min = 0;
        }
        $horizGridArray[] = $min;
        // USE OUR FUNCTION TO DETERMINE IDEAL Y AXIS SCALE INTERVAL
        $intervalFromZero = $this->determineAxisMarkerScale($this->data_max, $this->data_min);
        // IF WE HAVE POSITIVE VALUES, ADD GRID VALUES TO ARRAY
        // UNTIL WE REACH THE MAX NEEDED (WE WILL GO 1 OVER)
        $cur = $min;
        while ($cur < $this->data_max) {
            $cur += $intervalFromZero;
            $horizGridArray[] = $cur;
        }
        // IF WE HAVE NEGATIVE VALUES, ADD GRID VALUES TO ARRAY
        // UNTIL WE REACH THE MIN NEEDED (WE WILL GO 1 OVER)
        $cur = $min;
        while ($cur > $this->data_min) {
            $cur -= $intervalFromZero;
            $horizGridArray[] = $cur;
        }
        // SORT NEEDED B/C WE WILL USE LAST VALUE LATER (MAX)
        sort($horizGridArray);
        // DETERMINE VERTICAL GRID LINES
        $vertGridArray = [];
        $vertGrids = $this->data_count + 1;
        $interval = $this->bar_width + $this->space_width;
        // ASSEMBLE VERT GRIDLINE ARRAY
        for ($i = 1; $i < $vertGrids; ++$i) {
            $vertGridArray[] = $this->y_axis_x1 + ($interval * $i);
        }
        // LOOP THROUGH EACH HORIZONTAL LINE
        if ($this->bool_user_data_range) {
            $adjustment = $this->data_min * $this->unit_scale;
        } else {
            $adjustment = 0;
        }

        foreach ($horizGridArray as $value) {
            $yValue = (int) ($this->x_axis_y1 - ($value * $this->unit_scale) + $adjustment);
            if ($this->bool_grid) {
                imageline($this->image, $this->y_axis_x1, $yValue, $this->x_axis_x2, $yValue, $this->grid_color);
            }
            // DISPLAY VALUE ON Y AXIS IF DESIRED USING CALC'D GRID VALUES
            if ($this->bool_y_axis_values) {
                $adjustedYValue = $yValue - ($this->text_height / 2);
                $adjustedXValue = $this->y_axis_x1 - (strlen($value) * $this->text_width) - $this->axis_value_padding;
                imagestring($this->image, 2, $adjustedXValue, $adjustedYValue, $value, $this->y_axis_text_color);
            }
        }
        if (!$this->bool_all_positive && !$this->bool_user_data_range) {
            // RESET WITH BETTER VALUE BASED ON GRID MIN VALUE CALCULATIONS, NOT DATA MIN
            $this->y_axis_y1 = $this->x_axis_y1 - ($horizGridArray[0] * $this->unit_scale);
        }
        // RESET WITH BETTER VALUE BASED ON GRID VALUE CALCULATIONS, NOT DATA MIN
        $this->y_axis_y2 = $yValue;
        // LOOP THROUGH EACH VERTICAL LINE
        if ($this->bool_grid) {
            foreach ($vertGridArray as $value) {
                $xValue = $this->y_axis_y1;
                imageline($this->image, $value, $this->y_axis_y2, $value, $xValue, $this->grid_color);
            }
        }
        // DRAW GOAL LINES IF PRESENT (AFTER GRID) - DOESN'T GET EXECUTED IF ARRAY EMPTY
        foreach ($this->goal_line_array as $yLocation) {
            $yLocation = (int) ($this->x_axis_y1 - ($yLocation * $this->unit_scale) + $adjustment);
            imageline($this->image, $this->y_axis_x1, $yLocation, $this->x_axis_x2, $yLocation, $this->goal_line_color);
        }
    }

    public function generateDataPoints()
    {
        foreach ($this->data_point_array as $pointArray) {
            imagefilledellipse($this->image, $pointArray[0], $pointArray[1], $this->data_point_width, $this->data_point_width, $this->data_point_color);
        }
    }

    public function generateXAxis()
    {
        imageline($this->image, $this->x_axis_x1, $this->x_axis_y1, $this->x_axis_x2, $this->x_axis_y2, $this->x_axis_color);
    }

    public function generateYAxis()
    {
        imageline($this->image, $this->y_axis_x1, $this->y_axis_y1, $this->y_axis_x2, $this->y_axis_y2, $this->y_axis_color);
    }

    public function generateBackgound()
    {
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $this->background_color);
    }

    public function generateTitle()
    {
        // SPACING MAY HAVE CHANGED SINCE EARLIER
        // USE TOP MARGIN OR GRID TOP Y, WHICHEVER LESS
        $highestElement = ($this->top_margin < $this->y_axis_y2) ? $this->top_margin : $this->y_axis_y2;
        $textVertPos = ($highestElement / 2) - ($this->title_char_height / 2); // CENTERED
        $titleLength = strlen($this->title_text);
        if ($this->bool_title_center) {
            $this->title_x = ($this->width / 2) - (($titleLength * $this->title_char_width) / 2);
            $this->title_y = $textVertPos;
        } elseif ($this->bool_title_left) {
            $this->title_x = $this->y_axis_x1;
            $this->title_y = $textVertPos;
        } elseif ($this->bool_title_right) {
            $this->title_x = $this->x_axis_x2 - ($titleLength * $this->title_char_width);
            $this->title_y = $textVertPos;
        }
        imagestring($this->image, 2, $this->title_x, $this->title_y, $this->title_text, $this->title_color);
    }

    public function calcTopMargin()
    {
        if ($this->bool_title) {
            // INCLUDE SPACE FOR TITLE, APPROX MARGIN + 3*TITLE HEIGHT
            $this->top_margin = ($this->height * ($this->x_axis_default_percent / 100)) + $this->title_char_height;
        } else {
            // JUST USE DEFAULT SPACING
            $this->top_margin = $this->height * ($this->x_axis_default_percent / 100);
        }
    }

    public function calcRightMargin()
    {
        // JUST USE DEFAULT SPACING
        $this->right_margin = $this->width * ($this->y_axis_default_percent / 100);
    }

    public function calcCoords()
    {
        // CALCULATE AXIS POINTS, ALSO USED FOR OTHER CALCULATIONS
        $this->x_axis_x1 = $this->y_axis_margin;
        $this->x_axis_y1 = $this->height - $this->x_axis_margin;
        $this->x_axis_x2 = $this->width - $this->right_margin;
        $this->x_axis_y2 = $this->height - $this->x_axis_margin;
        $this->y_axis_x1 = $this->y_axis_margin;
        $this->y_axis_y1 = $this->height - $this->x_axis_margin;
        $this->y_axis_x2 = $this->y_axis_margin;
        $this->y_axis_y2 = $this->top_margin;
    }

    public function determineAxisMarkerScale($max, $min)
    {
        // FOR CALCLATION, TAKE RANGE OR MAX-0
        if ($this->bool_user_data_range) {
            $range = abs($max - $min);
        } else {
            $range = (abs($max - $min) > abs($max - 0)) ? abs($max - $min) : abs($max - 0);
        }
        // MULTIPLY UP TO OVER 100, TO BETTER FIGURE INTERVAL
        $count = 0;
        while (abs($range) < 100) {
            $range *= 10;
            ++$count;
        }
        // DIVIDE INTO INTERVALS BASED ON HEIGHT / PRESET CONSTANT - AFTER ROUNDING WILL BE APPROX
        $divisor = round($this->height / $this->range_divisor_factor);
        $divided = round($range / $divisor);
        $result = $this->roundUpOneExtraDigit($divided);
        // IF ROUNDED UP W/ EXTRA DIGIT IS MORE THAN 200% OF DIVIDED VALUE,
        // ROUND UP TO NEXT SIG NUMBER WITH SAME NUM OF DIGITS
        if ($result / $divided >= 2) {
            $result = $this->roundUpSameDigits($divided);
        }
        // DIVIDE BACK DOWN, IF NEEDED
        for ($i = 0; $i < $count; ++$i) {
            $result /= 10;
        }

        return $result;
    }

    public function roundUpSameDigits($num)
    {
        $len = strlen($num);
        if (round($num, -1 * ($len - 1)) == $num) {
            // WE ALREADY HAVE A SIG NUMBER
            return $num;
        }

        $firstDig = substr($num, 0, 1);
        $secondDig = substr($num, 1, 1);
        $rest = substr($num, 2);
        $secondDig = 5;
        $altered = $firstDig.$secondDig.$rest;

        // AFTER REASSEMBLY, ROUND UP TO NEXT SIG NUMBER, SAME # OF DIGITS
        return round((int) $altered, -1 * ($len - 1));
    }

    public function roundUpOneExtraDigit($num)
    {
        $len = strlen($num);
        $firstDig = substr($num, 0, 1);
        $rest = substr($num, 1);
        $firstDig = 5;
        $altered = $firstDig.$rest;

        // AFTER REASSEMBLY, ROUND UP TO NEXT SIG NUMBER, ONE EXTRA # OF DIGITS
        return round((int) $altered, -1 * $len);
    }

    public function displayErrors()
    {
        if (count($this->error) > 0) {
            $lineHeight = 12;
            $errorColor = imagecolorallocate($this->image, 0, 0, 0);
            $errorBackColor = imagecolorallocate($this->image, 255, 204, 0);
            imagefilledrectangle($this->image, 0, 0, $this->width - 1, 2 * $lineHeight, $errorBackColor);
            imagestring($this->image, 3, 2, 0, '!!----- PHPGraphLib Error -----!!', $errorColor);
            foreach ($this->error as $key => $errorText) {
                imagefilledrectangle($this->image, 0, ($key * $lineHeight) + $lineHeight, $this->width - 1, ($key * $lineHeight) + 2 * $lineHeight, $errorBackColor);
                imagestring($this->image, 2, 2, ($key * $lineHeight) + $lineHeight, '['.($key + 1).'] '.$errorText, $errorColor);
            }
            $errorOutlineColor = imagecolorallocate($this->image, 255, 0, 0);
            imagerectangle($this->image, 0, 0, $this->width - 1, ($key * $lineHeight) + 2 * $lineHeight, $errorOutlineColor);
        }
    }

    public function addData($data, $data2 = '', $data3 = '')
    {
        if (is_array($data)) {
            $this->data_array[] = $data;
        }
        if (is_array($data2)) {
            $this->data_array[] = $data2;
        }
        if (is_array($data3)) {
            $this->data_array[] = $data3;
        }

        // ASSESS DATA
        $min = $this->data_max_allowable;
        $max = $this->data_min_allowable;
        $nonZero = false;
        $this->data_count = 0;
        // GET RID OF BAD DATA, FIND MAX, MIN
        foreach ($this->data_array as $data_set_num => $data_set) {
            foreach ($data_set as $key => $item) {
                if (!is_numeric($item)) {
                    unset($this->data_array[$data_set_num][$key]);
                } else {
                    if ($item > 0 || $item < 0) {
                        $nonZero = true;
                    }
                    if ($item < $min) {
                        $min = $item;
                    }
                    if ($item > $max) {
                        $max = $item;
                    }
                }
            }
            $count = count($this->data_array[$data_set_num]);
            $count > $this->data_count ? $this->data_count = $count : null;
            if (!$nonZero || 0 == $this->data_count) {
                $this->error[] = 'Dataset '.($data_set_num + 1).' contains invalid data.';
            }
        }
        $set_count = count($this->data_array);
        $this->data_set_count = $set_count;
        if (0 == $set_count) {
            $this->error[] = 'No valid datasets added in adddata() function.';
        } else {
            $this->bool_data = true;
            // MIN AND MAX FOR ALL DATASETS
            $this->data_min = $min;
            $this->data_max = $max;
            if ($this->data_min >= 0) {
                $this->bool_all_positive = true;
            } elseif ($this->data_max <= 0) {
                $this->bool_all_negative = true;
            }
        }
    }

    public function setupXAxis($percent = '', $color = '')
    {
        if (false === $percent) {
            $this->bool_x_axis = false;
        } else {
            $this->bool_x_axis = true;
        }
        $this->bool_x_axis_setup = true;
        if (!empty($color) && $arr = $this->returnColorArray($color)) {
            $this->x_axis_color = imagecolorallocate($this->image, $arr[0], $arr[1], $arr[2]);
        }
        if (is_numeric($percent) && $percent > 0) {
            $percent = $percent / 100;
            $this->x_axis_margin = (int) ($this->height * $percent);
        } else {
            $percent = $this->x_axis_default_percent / 100;
            $this->x_axis_margin = (int) ($this->height * $percent);
        }
    }

    public function setupYAxis($percent = '', $color = '')
    {
        if (false === $percent) {
            $this->bool_y_axis = false;
        } else {
            $this->bool_y_axis = true;
        }
        $this->bool_y_axis_setup = true;
        if (!empty($color) && $arr = $this->returnColorArray($color)) {
            $this->y_axis_color = imagecolorallocate($this->image, $arr[0], $arr[1], $arr[2]);
        }
        if (is_numeric($percent) && $percent > 0) {
            $this->y_axis_margin = (int) ($this->width * ($percent / 100));
        } else {
            $percent = $this->y_axis_default_percent / 100;
            $this->y_axis_margin = (int) ($this->width * $percent);
        }
    }

    public function setRange($max, $min)
    {
        if ($min < 0 || $max <= 0) {
            $this->error[] = 'Range in setRange() must be non-negative.';
        } elseif (is_numeric($max) && is_numeric($min) && $max > $min) {
            $this->data_range_max = $max;
            $this->data_range_min = $min;
            $this->bool_user_data_range = true;
        } else {
            $this->error[] = 'Range in setRange() not specified properly. Consult documentation.';
        }
    }

    public function setTitle($title)
    {
        if (!empty($title)) {
            $this->title_text = $title;
            $this->bool_title = true;
        } else {
            $this->error[] = 'String arg for setTitle() not specified properly.';
        }
    }

    public function setTitleLocation($location)
    {
        $this->bool_title_left = false;
        $this->bool_title_right = false;
        $this->bool_title_center = false;

        switch (strtolower($location)) {
            case 'left': $this->bool_title_left = true;

                break;

            case 'right': $this->bool_title_right = true;

                break;

            case 'center': $this->bool_title_center = true;

                break;

            default: $this->error[] = 'String arg for setTitleLocation() not specified properly.';
        }
    }

    public function setBars($bool)
    {
        if (is_bool($bool)) {
            $this->bool_bars = $bool;
        } else {
            $this->error[] = 'Boolean arg for setBars() not specified properly.';
        }
    }

    public function setGrid($bool)
    {
        if (is_bool($bool)) {
            $this->bool_grid = $bool;
        } else {
            $this->error[] = 'Boolean arg for setGrid() not specified properly.';
        }
    }

    public function setXValues($bool)
    {
        if (is_bool($bool)) {
            $this->bool_x_axis_values = $bool;
        } else {
            $this->error[] = 'Boolean arg for setXValues() not specified properly.';
        }
    }

    public function setYValues($bool)
    {
        if (is_bool($bool)) {
            $this->bool_y_axis_values = $bool;
        } else {
            $this->error[] = 'Boolean arg for setYValues() not specified properly.';
        }
    }

    public function setXValuesHorizontal($bool)
    {
        if (is_bool($bool)) {
            ($bool) ? $this->bool_x_axis_values_vert = false : $this->bool_x_axis_values_vert = true;
        } else {
            $this->error[] = 'Boolean arg for setXValuesHorizontal() not specified properly.';
        }
    }

    public function setXValuesVertical($bool)
    {
        if (is_bool($bool)) {
            $this->bool_x_axis_values_vert = $bool;
        } else {
            $this->error[] = 'Boolean arg for setXValuesVertical() not specified properly.';
        }
    }

    public function setBarOutline($bool)
    {
        if (is_bool($bool)) {
            $this->bool_bar_outline = $bool;
        } else {
            $this->error[] = 'Boolean arg for setBarOutline() not specified properly.';
        }
    }

    public function setDataPoints($bool)
    {
        if (is_bool($bool)) {
            $this->bool_data_points = $bool;
        } else {
            $this->error[] = 'Boolean arg for setDataPoints() not specified properly.';
        }
    }

    public function setDataPointSize($size)
    {
        if (is_numeric($size)) {
            $this->data_point_width = $size;
        } else {
            $this->error[] = 'Data point size in setDataPointSize() not specified properly.';
        }
    }

    public function setDataValues($bool)
    {
        if (is_bool($bool)) {
            $this->bool_data_values = $bool;
        } else {
            $this->error[] = 'Boolean arg for setDataValues() not specified properly.';
        }
    }

    public function setLine($bool)
    {
        if (is_bool($bool)) {
            $this->bool_line = $bool;
        } else {
            $this->error[] = 'Boolean arg for setLine() not specified properly.';
        }
    }

    public function setGoalLine($yValue)
    {
        if (is_numeric($yValue)) {
            $this->goal_line_array[] = $yValue;
        } else {
            $this->error[] = 'Goal line Y axis value not specified properly.';
        }
    }

    // -------------"PRIVATE" COLOR HANDLING FUNCTIONS---------------//
    public function allocateColors()
    {
        $this->background_color = imagecolorallocate($this->image, 255, 255, 255);
        $this->grid_color = imagecolorallocate($this->image, 220, 220, 220);
        $this->bar_color = imagecolorallocate($this->image, 200, 200, 200);
        $this->line_color_default = imagecolorallocate($this->image, 100, 100, 100);
        $this->x_axis_text_color = $this->line_color_default;
        $this->y_axis_text_color = $this->line_color_default;
        $this->data_value_color = $this->line_color_default;
        $this->title_color = imagecolorallocate($this->image, 0, 0, 0);
        $this->outline_color = $this->title_color;
        $this->data_point_color = $this->title_color;
        $this->x_axis_color = $this->title_color;
        $this->y_axis_color = $this->title_color;
        $this->goal_line_color = $this->title_color;
        // New Legend Colors
        $this->legend_outline_color = $this->grid_color;
        $this->legend_color = $this->background_color;
        $this->legend_text_color = $this->line_color_default;
        $this->legend_swatch_outline_color = $this->line_color_default;
    }

    public function returnColorArray($color)
    {
        // CHECK TO SEE IF NUMERIC COLOR PASSED THROUGH IN FORM '128,128,128'
        if (false !== strpos($color, ',')) {
            return explode(',', $color);
        }

        switch (strtolower($color)) {
            // NAMED COLORS BASED ON W3C's RECOMMENDED HTML COLORS
            case 'black': return [0, 0, 0];

                break;

            case 'silver': return [192, 192, 192];

                break;

            case 'gray': return [128, 128, 128];

                break;

            case 'white': return [255, 255, 255];

                break;

            case 'maroon': return [128, 0, 0];

                break;

            case 'red': return [255, 0, 0];

                break;

            case 'purple': return [128, 0, 128];

                break;

            case 'fuscia': return [255, 0, 255];

                break;

            case 'green': return [0, 128, 0];

                break;

            case 'lime': return [0, 255, 0];

                break;

            case 'olive': return [128, 128, 0];

                break;

            case 'yellow': return [255, 255, 0];

                break;

            case 'navy': return [0, 0, 128];

                break;

            case 'blue': return [0, 0, 255];

                break;

            case 'teal': return [0, 128, 128];

                break;

            case 'aqua': return [0, 255, 255];

                break;
        }
        $this->error[] = "Color name \"{$color}\" not recogized.";

        return false;
    }

    public function allocateGradientColors($color1R, $color1G, $color1B, $rScale, $gScale, $bScale, $num, $data_set_num)
    {
        // CALUCLATE THE COLORS USED IN OUR GRADIENT AND STORE THEM IN ARRAY
        $this->gradient_color_array[$data_set_num] = [];
        for ($i = 0; $i <= $num + 1; ++$i) {
            $this->gradient_color_array[$data_set_num][$i] = imagecolorallocate($this->image, $color1R - ($rScale * $i), $color1G - ($gScale * $i), $color1B - ($bScale * $i));
        }
        $this->bool_gradient_colors_found[$data_set_num] = true;
    }

    public function setGenericColor($inputColor, $var, $errorMsg)
    {
        // CAN BE USED FOR MOST COLOR SETTING OPTIONS
        if (!empty($inputColor) && $arr = $this->returnColorArray($inputColor)) {
            eval($var.' = imagecolorallocate($this->image, $arr[0], $arr[1], $arr[2]);');

            return true;
        }

        $this->error[] = $errorMsg;

        return false;
    }

    // -------------------"PUBLIC" COLOR FUNCTIONS----------------------//
    public function setBackgroundColor($color)
    {
        if ($this->setGenericColor($color, '$this->background_color', 'Background color not specified properly.')) {
            $this->bool_background = true;
        }
    }

    public function setTitleColor($color)
    {
        $this->setGenericColor($color, '$this->title_color', 'Title color not specified properly.');
    }

    public function setTextColor($color)
    {
        $this->setGenericColor($color, '$this->x_axis_text_color', 'X axis text color not specified properly.');
        $this->setGenericColor($color, '$this->y_axis_text_color', 'Y axis Text color not specified properly.');
    }

    public function setXAxisTextColor($color)
    {
        $this->setGenericColor($color, '$this->x_axis_text_color', 'X axis text color not specified properly.');
    }

    public function setYAxisTextColor($color)
    {
        $this->setGenericColor($color, '$this->y_axis_text_color', 'Y axis Text color not specified properly.');
    }

    public function setBarColor($color, $color2 = '', $color3 = '')
    {
        $this->setGenericColor($color, '$this->multi_bar_colors[]', 'Bar color not specified properly.');
        if (!empty($color2)) {
            $this->setGenericColor($color2, '$this->multi_bar_colors[]', 'Bar color 2 not specified properly.');
        }
        if (!empty($color3)) {
            $this->setGenericColor($color3, '$this->multi_bar_colors[]', 'Bar color 3 not specified properly.');
        }
    }

    public function setGridColor($color)
    {
        $this->setGenericColor($color, '$this->grid_color', 'Grid color not specified properly.');
    }

    public function setBarOutlineColor($color)
    {
        $this->setGenericColor($color, '$this->outline_color', 'Bar outline color not specified properly.');
    }

    public function setDataPointColor($color)
    {
        $this->setGenericColor($color, '$this->data_point_color', 'Data point color not specified properly.');
    }

    public function setDataValueColor($color)
    {
        $this->setGenericColor($color, '$this->data_value_color', 'Data value color not specified properly.');
    }

    public function setLineColor($color, $color2 = '', $color3 = '')
    {
        $this->setGenericColor($color, '$this->line_color[]', 'Line color not specified properly.');
        if (!empty($color2)) {
            $this->setGenericColor($color2, '$this->line_color[]', 'Line color 2 not specified properly.');
        }
        if (!empty($color3)) {
            $this->setGenericColor($color3, '$this->line_color[]', 'Line color 3 not specified properly.');
        }
    }

    public function setGoalLineColor($color)
    {
        $this->setGenericColor($color, '$this->goal_line_color', 'Goal line color not specified properly.');
    }

    public function setGradient($color1, $color2, $color3 = '', $color4 = '', $color5 = '', $color6 = '')
    {
        if (!empty($color1) && !empty($color2) && ($arr1 = $this->returnColorArray($color1)) && ($arr2 = $this->returnColorArray($color2))) {
            $this->bool_gradient = true;
            $this->multi_gradient_colors_1[] = $arr1;
            $this->multi_gradient_colors_2[] = $arr2;
        } else {
            $this->error[] = 'Gradient color(s) not specified properly.';
        }
        // GRADIENTS 3,4,5,6 OPTIONAL
        if (!empty($color3) && !empty($color4) && ($arr1 = $this->returnColorArray($color3)) && ($arr2 = $this->returnColorArray($color4))) {
            $this->bool_gradient = true;
            $this->multi_gradient_colors_1[] = $arr1;
            $this->multi_gradient_colors_2[] = $arr2;
        }
        if (!empty($color5) && !empty($color6) && ($arr1 = $this->returnColorArray($color5)) && ($arr2 = $this->returnColorArray($color6))) {
            $this->bool_gradient = true;
            $this->multi_gradient_colors_1[] = $arr1;
            $this->multi_gradient_colors_2[] = $arr2;
        }
    }

    // Legend Related Functions
    public function setLegend($bool)
    {
        if (is_bool($bool)) {
            $this->bool_legend = $bool;
        } else {
            $this->error[] = 'Boolean arg for setLegend() not specified properly.';
        }
    }

    public function setLegendColor($color)
    {
        $this->setGenericColor($color, '$this->legend_color', 'Legend color not specified properly.');
    }

    public function setLegendTextColor($color)
    {
        $this->setGenericColor($color, '$this->legend_text_color', 'Legend text color not specified properly.');
    }

    public function setLegendOutlineColor($color)
    {
        $this->setGenericColor($color, '$this->legend_outline_color', 'Legend outline color not specified properly.');
    }

    public function setSwatchOutlineColor($color)
    {
        $this->setGenericColor($color, '$this->legend_swatch_outline_color', 'Swatch outline color not specified properly.');
    }

    public function setLegendTitle($title, $title2 = '', $title3 = '')
    {
        if (!empty($title)) {
            $len = strlen($title);
            if ($len > $this->legend_max_chars) {
                $title = substr($title, 0, $this->legend_max_chars);
                $this->legend_total_chars[] = $this->legend_max_chars;
            } else {
                $this->legend_total_chars[] = $len;
            }
            $this->legend_titles[] = $title;
        } else {
            $this->error[] = 'String arg 1 for setLegendTitles() not specified properly.';
        }
        if (!empty($title2)) {
            $len = strlen($title2);
            if ($len > $this->legend_max_chars) {
                $title2 = substr($title2, 0, $this->legend_max_chars);
                $this->legend_total_chars[] = $this->legend_max_chars;
            } else {
                $this->legend_total_chars[] = $len;
            }
            $this->legend_titles[] = $title2;
        }
        if (!empty($title3)) {
            $len = strlen($title3);
            if ($len > $this->legend_max_chars) {
                $title3 = substr($title3, 0, $this->legend_max_chars);
                $this->legend_total_chars[] = $this->legend_max_chars;
            } else {
                $this->legend_total_chars[] = $len;
            }
            $this->legend_titles[] = $title3;
        }
    }

    public function generateLegend()
    {
        $swatchToTextOffset = ($this->legend_text_height - 6) / 2;
        $swatchSize = $this->legend_text_height - 2 * $swatchToTextOffset;
        // CALC HEIGHT / WIDTH BASED ON # OF DATA SETS
        $this->legend_height = $this->legend_text_height + (2 * $this->legend_padding);
        $totalChars = 0;
        for ($i = 0; $i < $this->data_set_count; ++$i) {
            // COULD HAVE MORE TITLES THAN DATA SETS - CHECK FOR THIS
            if (isset($this->legend_total_chars[$i])) {
                $totalChars += $this->legend_total_chars[$i];
            }
        }
        $this->legend_width = $totalChars * $this->legend_text_width + ($this->legend_padding * 1.5)
            + ($this->data_set_count * ($swatchSize + ($this->legend_padding * 2)));
        $this->legend_x = $this->x_axis_x2 - $this->legend_width;
        $highestElement = ($this->top_margin < $this->y_axis_y2) ? $this->top_margin : $this->y_axis_y2;
        $this->legend_y = ($highestElement / 2) - ($this->legend_height / 2); // CENTERED

        // BACKGROUND
        imagefilledrectangle(
            $this->image,
            $this->legend_x,
            $this->legend_y,
            $this->legend_x + $this->legend_width,
            $this->legend_y + $this->legend_height,
            $this->legend_color
        );
        // BORDER
        imagerectangle(
            $this->image,
            $this->legend_x,
            $this->legend_y,
            $this->legend_x + $this->legend_width,
            $this->legend_y + $this->legend_height,
            $this->legend_outline_color
        );

        $length_covered = 0;
        for ($i = 0; $i < $this->data_set_count; ++$i) {
            $data_label = '';
            if (isset($this->legend_titles[$i])) {
                $data_label = $this->legend_titles[$i];
            }
            $yValue = $this->legend_y + $this->legend_padding;
            $xValue = $this->legend_x + $this->legend_padding + ($length_covered * $this->legend_text_width) + ($i * 4 * $this->legend_padding);
            $length_covered += strlen($data_label);
            // DRAW COLOR BOXES
            if ($this->bool_bars) {
                if ($this->bool_gradient) {
                    $color = $this->gradient_color_array[$this->data_set_count - $i - 1][0];
                } else {
                    $color = $this->multi_bar_colors[$this->data_set_count - $i - 1];
                }
            } elseif ($this->bool_line && !$this->bool_bars) {
                $color = $this->line_color[$this->data_set_count - $i - 1];
            }

            imagefilledrectangle($this->image, $xValue, $yValue + $swatchToTextOffset, $xValue + $swatchSize, $yValue + $swatchToTextOffset + $swatchSize, $color);
            imagerectangle($this->image, $xValue, $yValue + $swatchToTextOffset, $xValue + $swatchSize, $yValue + $swatchToTextOffset + $swatchSize, $this->legend_swatch_outline_color);
            imagestring($this->image, 2, $xValue + (2 * $this->legend_padding + 2), $yValue, $data_label, $this->legend_text_color);
        }
    }
}
