<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfGenderAnalysisInsight.php
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
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Test of GenderAnalysisInsight
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/genderanalysis.php';

class TestOfGenderAnalysisInsight extends ThinkUpInsightUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testGenderAnalysisForFacebookWomenReact() {
        // Get data ready that insight requires
        $builders = self::buildData('female');
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '9654321';
        $instance->network_username = 'Gloria Steinem';
        $instance->network = 'facebook';
        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
            $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = $number_days,
            $iterator = false, $is_public = false);

        $insight_plugin = new GenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 1);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('gender_analysis', 100, $today);
        $gender_data = unserialize($result->related_data);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual($result->headline, 'Women reacted to Gloria Steinem\'s status updates the most.');
        $this->assertIsA($gender_data, "array");
        $this->assertEqual(count($gender_data), 1);

        $this->assertEqual($gender_data['pie_chart']['female'], 4);
        $this->assertEqual($gender_data['pie_chart']['male'], 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testGenderAnalysisForTwitterWomenReact() {
        // Get data ready that insight requires
        $builders = self::buildData('female', 'twitter');
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '9654321';
        $instance->network_username = 'Gloria Steinem';
        $instance->network = 'twitter';
        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
            $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = $number_days,
            $iterator = false, $is_public = false);

        $insight_plugin = new GenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 1);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('gender_analysis', 100, $today);
        // Don't generate an insight for Twitter
        $this->assertNull($result);
    }

    public function testGenderAnalysisForFacebookMenReact() {
        // Get data ready that insight requires
        $builders = self::buildData('male');
        //sleep(1000);
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '8654321';
        $instance->network_username = 'Gloria Steinem';
        $instance->network = 'facebook';
        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
            $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = $number_days,
            $iterator = false, $is_public = false);

        $insight_plugin = new GenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 1);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('gender_analysis', 100, $today);
        $gender_data = unserialize($result->related_data);

        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual($result->headline, 'Men reacted to Gloria Steinem\'s status updates the most.');
        $this->assertIsA($gender_data, "array");
        $this->assertEqual(count($gender_data), 1);
        $this->assertEqual($gender_data['pie_chart']['female'], 2);
        $this->assertEqual($gender_data['pie_chart']['male'], 3);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testGenderAnalysisForFacebookEqualReaction() {
        // Get data ready that insight requires
        $builders = self::buildData('all');
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '8654321';
        $instance->network_username = 'Gloria Steinem';
        $instance->network = 'facebook';

        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
            $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = $number_days,
            $iterator = false, $is_public = false);

        $insight_plugin = new GenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 1);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('gender_analysis', 100, $today);
        $gender_data = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual($result->headline, 'Both genders reacted to Gloria Steinem\'s status updates equally.');
        $this->assertIsA($gender_data, "array");

        $this->assertEqual(count($gender_data), 1);
        $this->assertEqual($gender_data['pie_chart']['female'], 2);
        $this->assertEqual($gender_data['pie_chart']['male'], 2);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testGenderAnalysisForFacebookLessThanThreeGenderDataBits() {
        // Get data ready that insight requires
        $builders = array();

        // Build users
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654321', 'user_name'=>'Gloria Steinem',
            'full_name'=>'Gloria Steinem', 'gender'=>'female', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
            'network'=>$network));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654320', 'user_name'=>'Abraham Lincoln',
            'full_name'=>'Abraham Lincoln', 'gender'=>'male', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
            'network'=>$network));

        // Posts
        $builders[] = FixtureBuilder::build('posts', array('id'=>333, 'post_id'=>333,
            'author_user_id'=>'9654321', 'author_username'=>'Gloria Steinem', 'author_fullname'=>'Gloria Steinem',
            'author_avatar'=>'avatar.jpg', 'network'=>$network, 'post_text'=>'This is a simple post.',
            'pub_date'=>'-1d' , 'reply_count_cache'=>3, 'is_protected'=>0,'favlike_count_cache' => 2));

        $builders[] = FixtureBuilder::build('posts', array('id'=>334, 'post_id'=>334,
            'author_user_id'=>'9654321', 'author_username'=>'Gloria Steinem', 'author_fullname'=>'Gloria Steinem',
            'author_avatar'=>'avatar.jpg', 'network'=>$network, 'post_text'=>'This is a simple comment.',
            'pub_date'=>'-2h', 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
            'in_reply_to_post_id' => 333));

        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '8654321';
        $instance->network_username = 'Gloria Steinem';
        $instance->network = 'facebook';

        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
            $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = $number_days,
            $iterator = false, $is_public = false);

        $insight_plugin = new GenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 1);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('gender_analysis', 100, $today);
        $this->assertNull($result);
    }

    private function buildData($gender = 'all', $network = 'facebook') {
        $builders = array();

        // Build users
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654321', 'user_name'=>'Gloria Steinem',
            'full_name'=>'Gloria Steinem', 'gender'=>'female', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
            'network'=>$network));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654320', 'user_name'=>'Abraham Lincoln',
            'full_name'=>'Abraham Lincoln', 'gender'=>'male', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
            'network'=>$network));

        // Posts
        $builders[] = FixtureBuilder::build('posts', array('id'=>333, 'post_id'=>333,
            'author_user_id'=>'9654321', 'author_username'=>'Gloria Steinem', 'author_fullname'=>'Gloria Steinem',
            'author_avatar'=>'avatar.jpg', 'network'=>$network, 'post_text'=>'This is a simple post.',
            'pub_date'=>'-1d' , 'reply_count_cache'=>3, 'is_protected'=>0,'favlike_count_cache' => 2));

        // Replies
        $builders[] = FixtureBuilder::build('posts', array('id'=>334, 'post_id'=>334,
            'author_user_id'=>'9654321', 'author_username'=>'Gloria Steinem', 'author_fullname'=>'Gloria Steinem',
            'author_avatar'=>'avatar.jpg', 'network'=>$network, 'post_text'=>'This is a simple comment.',
            'pub_date'=>'-2h', 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
            'in_reply_to_post_id' => 333));

        switch ($gender) {
            case "female":
                $user_id = '9654321';
                $user_name = 'Gloria Steinem';
                break;
            case "male":
                $user_id = '9654320';
                $user_name = 'Abraham Lincoln';
                break;
            case "all":
                $user_id = null;
                $user_name = null;
                break;
        }
        if (isset($user_id)) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>335, 'post_id'=>335,
                'author_user_id'=>$user_id, 'author_username'=>$user_name, 'author_fullname'=>$user_name,
                'author_avatar'=>'avatar.jpg', 'network'=>$network, 'post_text'=>'This is a simple comment 1.',
                'pub_date'=>'-2h', 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
                'in_reply_to_post_id' => 333));

            $builders[] = FixtureBuilder::build('posts', array('id'=>336, 'post_id'=>336,
                'author_user_id'=>$user_id, 'author_username'=>$user_name, 'author_fullname'=>$user_name,
                'author_avatar'=>'avatar.jpg', 'network'=>$network, 'post_text'=>'This is a simple comment 2.',
                'pub_date'=>'-3h', 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
                'in_reply_to_post_id' => 333));
        } else {
            $builders[] = FixtureBuilder::build('posts', array('id'=>335, 'post_id'=>335,
                'author_user_id'=>'9654320', 'author_username'=>'Abraham Lincoln', 'author_fullname'=>'Abraham Lincoln',
                'author_avatar'=>'avatar.jpg', 'network'=>$network, 'post_text'=>'This is a simple comment 1.',
                'pub_date'=>'-2h', 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
                'in_reply_to_post_id' => 333));
        }

        //Favorites
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>333, 'author_user_id'=>'9654321',
            'fav_of_user_id'=>'9654321', 'network'=>$network));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>333, 'author_user_id'=>'9654321',
            'fav_of_user_id'=>'9654320', 'network'=>$network));

        return $builders;
    }
}
