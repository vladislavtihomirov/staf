<?php

class SegmABReportController extends AbstractController
{
    const RESOURCE = 'segm_ab_report_campaigns';
    const ID = 'campaign_id';
    public function __construct(\Slim\Container $container)
    {
        parent::__construct($container);
    }

    public function __call($name, $arguments)
    {
        return parent::__call($name, $arguments);
    }

    protected function get()
    {
		$sql = 
		
		"SELECT 
			MIN(fst.date::timestamp) as \"Date\",
			(fth.name || '(' || (fst.campaign_id::text) || ')' ) as \"Campaign\",
			(SELECT name from public.mi_cohorts where cohort_id = fth.cohort_id) || '(' || (fth.cohort_id::text) || ')' as \"Cohort\",
			SUM(fst.sent_cnt) as \"Sent\",
			SUM(fst.open_cnt) as \"Open\",
			SUM(fst.click_cnt) as \"Click\",            
			SUM(fst.open_cnt::real / (CASE fst.sent_cnt WHEN 0 THEN null ELSE fst.sent_cnt END)::real) as \"Open-sent rate\",
            SUM(fst.click_cnt::real / (CASE fst.open_cnt WHEN 0 THEN null ELSE fst.open_cnt END)::real) as \"Click-open rate\",
            SUM(fst.click_cnt::real / (CASE fst.sent_cnt WHEN 0 THEN null ELSE fst.sent_cnt END)::real) as \"Click-sent x100 rate\",
			snd.content_id as \"Content ID\",
			fth.objective as \"Objective\",
    		CASE snd.channel_id WHEN 1 THEN trd.title ELSE trd.template END as \"Subject\"
		FROM 
			public.mi_report_jobs_campaigns_actions_contents fst
			JOIN public.mi_campaign_actions snd
			on fst.action_id = snd.action_id
			JOIN public.mi_content_locales trd
			on fst.content_id = trd.content_id
			JOIN public.mi_campaigns fth
			on fst.campaign_id = fth.campaign_id
		WHERE " . urldecode($this->params['campaigns']) . " 
		GROUP BY \"Content ID\", \"Campaign\", \"Cohort\", \"Objective\", \"Subject\"";

        try {
            $stmt = $this->container->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->response = $this->response->withStatus(500);
         	$results = ['status' => 'error', 'message' => $e->getMessage(), '_q' => [$sql, $this->query_vars]];
        }
		return ['data' => $results];
    }
}