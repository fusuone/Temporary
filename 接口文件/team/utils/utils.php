<?php
/**
 * 工具类
 */

class Utils {
  public static function arrayToXml($arr) {
    $xml = '<xml>';
    foreach ($arr as $key => $val) {
      if (is_numeric($val)) {
        $xml .= '<'.$key.'>'.$val.'</'.$key.'>';
      } else {
        $xml .= '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
      }
    }
    $xml .= '</xml>';
    return $xml;
  }

  public static function xmlToArray($xml) {
    // 禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $values;
  }
}