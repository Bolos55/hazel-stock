<?php
/**
 * Work Date Helper
 * 
 * ระบบวันทำงานที่รีเซ็ทตอนตี 3 แทนเที่ยงคืน
 * เพราะงานอาจจะข้ามวันไปถึงตี 2-3
 * 
 * กฎ:
 * - ก่อนตี 3 (00:00-02:59) → ยังนับเป็นวันเมื่อวาน
 * - หลังตี 3 (03:00-23:59) → เริ่มวันใหม่
 */

/**
 * Get current work date (resets at 3 AM instead of midnight)
 * 
 * @return string Date in Y-m-d format
 */
function getWorkDate() {
    $now = new DateTime();
    $hour = (int)$now->format('H');
    
    // If before 3 AM, use yesterday's date
    if ($hour < 3) {
        $now->modify('-1 day');
    }
    
    return $now->format('Y-m-d');
}

/**
 * Get work date from a specific timestamp
 * 
 * @param int|string $timestamp Unix timestamp or date string
 * @return string Date in Y-m-d format
 */
function getWorkDateFromTimestamp($timestamp) {
    if (is_string($timestamp)) {
        $dt = new DateTime($timestamp);
    } else {
        $dt = new DateTime();
        $dt->setTimestamp($timestamp);
    }
    
    $hour = (int)$dt->format('H');
    
    // If before 3 AM, use yesterday's date
    if ($hour < 3) {
        $dt->modify('-1 day');
    }
    
    return $dt->format('Y-m-d');
}

/**
 * Get current work datetime for display
 * 
 * @return array ['date' => 'Y-m-d', 'time' => 'H:i:s', 'is_next_day' => bool]
 */
function getWorkDateTime() {
    $now = new DateTime();
    $hour = (int)$now->format('H');
    $isNextDay = $hour < 3;
    
    $workDate = getWorkDate();
    
    return [
        'date' => $workDate,
        'time' => $now->format('H:i:s'),
        'datetime' => $now->format('Y-m-d H:i:s'),
        'is_next_day' => $isNextDay,
        'actual_date' => $now->format('Y-m-d')
    ];
}

/**
 * Check if current time is in "next day" period (00:00-02:59)
 * 
 * @return bool
 */
function isNextDayPeriod() {
    $hour = (int)date('H');
    return $hour < 3;
}

/**
 * Get work date display text in Thai
 * 
 * @return string
 */
function getWorkDateDisplayThai() {
    $workDate = getWorkDate();
    $dt = new DateTime($workDate);
    
    $thaiDate = $dt->format('d/m/') . ((int)$dt->format('Y') + 543);
    
    if (isNextDayPeriod()) {
        $thaiDate .= ' (ยังนับเป็นวันเมื่อวาน)';
    }
    
    return $thaiDate;
}
