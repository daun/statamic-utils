<?php

namespace Daun\StatamicUtils\Cache;

class QueryParams
{
    public static function toIgnore(): array
    {
        return [
            ...self::tracking(),
            ...self::functional(),
        ];
    }

    public static function tracking(): array
    {
        return [
            '_bta_c',
            '_bta_tid',
            '_ga',
            '_gl',
            '_ke',
            '_kx',
            'adgroupid',
            'adid',
            'adt_ei',
            'campaignid',
            'ck_subscriber_id',
            'dclid',
            'ef_id',
            'epik',
            'fb_action_ids',
            'fb_action_types',
            'fb_source',
            'fbclid',
            'gad',
            'gad_campaignid',
            'gad_source',
            'gbraid',
            'gclid',
            'gclsrc',
            'gdffi',
            'gdfms',
            'gdftrk',
            'li_fat_id',
            'mc_cid',
            'mc_eid',
            'mkt_tok',
            'mkwid',
            'msclkid',
            'mtm_campaign',
            'mtm_cid',
            'mtm_content',
            'mtm_group',
            'mtm_keyword',
            'mtm_kwd',
            'mtm_medium',
            'mtm_placement',
            'mtm_source',
            'pcrid',
            'pp',
            'rdt_cid',
            'ref',
            's_kwcid',
            'ScCid',
            'sh_kit',
            'srsltid',
            'sscid',
            'ttclid',
            'twclid',
            'usqp',
            'utm_campaign',
            'utm_content',
            'utm_creative_format',
            'utm_expid',
            'utm_id',
            'utm_marketing_tactic',
            'utm_medium',
            'utm_source',
            'utm_source_platform',
            'utm_term',
            'vgo_ee',
            'wbraid',
            'yclid',
        ];
    }

    public static function functional(): array
    {
        return [
            '_token',
            'age-verified',
            'ao_noptimize',
            'cn-reloaded',
        ];
    }
}
