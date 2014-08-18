<?php
/*
 * Plugin Name: Gender Analysis
 * Description: Gender breakdown of commentors and likers on your Facebook status updates last week.
 * When: Fridays for Facebook
 */

/**
 * ThinkUp/webapp/plugins/insightsgenerator/insights/genderanalysis.php
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * GenderAnalysisInsight
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */

class GenderAnalysisInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance,  User $user, $last_week_of_posts, $number_days) {
        if ($instance->network == 'facebook') {
            parent::generateInsight ( $instance, $user, $last_week_of_posts, $number_days );
            $this->logger->logInfo ( "Begin generating insight", __METHOD__ . ',' . __LINE__ );

            $should_generate_insight = self::shouldGenerateWeeklyInsight( 'gender_analysis', $instance, null, false,
                $day_of_week=5, null, $excluded_networks = array('twitter', 'instagram', 'foursquare', 'google+') );

            if ($should_generate_insight) {
                $fav_post_dao = DAOFactory::getDAO ('FavoritePostDAO');

                $female_likes_total = 0;
                $male_likes_total = 0;
                $female_comments_total = 0;
                $male_comments_total = 0;
                foreach ( $last_week_of_posts as $post ) {
                    $gender_fav = $fav_post_dao->getGenderOfFavoriters ( $post->post_id );
                    //print_r($gender_fav);
                    if (isset($gender_fav['female_likes_count'])) {
                        $female_likes_total = $female_likes_total + intval($gender_fav['female_likes_count']);
                    }
                    if (isset($gender_fav['male_likes_count'])) {
                        $male_likes_total = $male_likes_total + intval($gender_fav['male_likes_count']);
                    }
                    $gender_comm = $fav_post_dao->getGenderOfCommenters ( $post->post_id );
                    //print_r($gender_comm);
                    if (isset($gender_comm['female_comment_count'])) {
                        $female_comments_total = $female_comments_total + intval($gender_comm['female_comment_count']);
                    }
                    if (isset($gender_comm['male_comment_count'])) {
                        $male_comments_total = $male_comments_total + intval($gender_comm['male_comment_count']);
                    }
                }

                $this->logger->logInfo ( "$female_likes_total female likes", __METHOD__ . ',' . __LINE__ );
                $this->logger->logInfo ( "$female_comments_total female comments", __METHOD__ . ',' . __LINE__ );
                $this->logger->logInfo ( "$male_likes_total male likes", __METHOD__ . ',' . __LINE__ );
                $this->logger->logInfo ( "$male_comments_total male comments", __METHOD__ . ',' . __LINE__ );
                $female_total = $female_likes_total + $female_comments_total;
                $male_total = $male_likes_total + $male_comments_total;
                $total_gender_data = $female_total + $male_total;
                $this->logger->logInfo ( "Of $total_gender_data bits of gender data for post $post->post_id, ".
                    "$female_total were female and $male_total were male", __METHOD__ . ',' . __LINE__ );

                // Only generate this insight if there is gender data for at least 3 comments and/or likes
                if ($total_gender_data >= 3) {
                    $gender_data = array (
                        'gender' => 'value',
                        'female' => $female_total,
                        'male' => $male_total
                    );

                    if ($female_total > $male_total) {
                        $headline = "Women reacted to $this->username's status updates the most.";
                        $text = "This past week, <strong>" . number_format ( $female_total ) .
                            " likes and comments</strong> on " . $instance->network_username .
                            "'s status updates were by people who identify as female, compared to ".
                            number_format($male_total). " by people who identify as male.";
                    } elseif ($male_total > $female_total) {
                        $headline = "Men reacted to $this->username's status updates the most.";
                        $text = "This past week, <strong>" . number_format ( $male_total ) .
                            " likes and comments</strong> on " . $instance->network_username .
                            "'s status updates were by people who identify as male, compared to ".
                            number_format($female_total)." by people who identify as female.";
                    } else {
                        $headline = "Both genders reacted to $this->username's status updates equally.";
                        $text = "This week, people who identify as male and female liked and commented on ".
                            "$this->username's status updates at the same rate.";
                    }

                    $my_insight = new Insight();
                    $my_insight->slug = 'gender_analysis'; //slug to label this insight's content
                    $my_insight->instance_id = $instance->id;
                    $my_insight->headline = $headline;
                    $my_insight->text = $text;
                    $my_insight->date = $this->insight_date;
                    $my_insight->filename = basename(__FILE__, ".php");
                    $my_insight->emphasis = Insight::EMPHASIS_HIGH;
                    $my_insight->setPieChart($gender_data);

                    $this->insight_dao->insertInsight($my_insight);
                } else {
                    $this->logger->logInfo ( "Not enough gender data to generate insight", __METHOD__ . ','
                        . __LINE__ );
                }
                $this->logger->logInfo ( "Done generating insight", __METHOD__ . ',' . __LINE__ );
            } else {
                $this->logger->logInfo ( "Not generating insight for non-Facebook network", __METHOD__ . ','
                    . __LINE__ );
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('GenderAnalysisInsight');
