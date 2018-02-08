<?php



$cardinal->mustLogin();


$page_title="";

if ($GET["myReward"])
{
  $reward = $db->where("user_id", $user["id"])->where("reward_id", $GET["myReward"])->getOne("user_rewards");

  if ($reward["reward_id"])
  {

    if (!$reward["received"] && $_POST["receive"])
    {
  	  if ($uclass->claimReward($reward['reward_id'], $reward))
  	  {
        	$_SESSION["messenger"] = array("message" => "Reward confirmed and received", "type" => "success");
       	 $cardinal->redirect($url);
  	  } else $errors[] = "An error took place";
    }

  	$reward["achievements"] = $reward["achievements"] ? unserialize($reward["achievements"]) : false;

      if ($reward["skills"])
      {
        $tVars["theskills"] = $theskills;

        $reward["skills"] = unserialize($reward["skills"]);

      }

      if (is_array($reward["achievements"]))
        foreach($reward["achievements"] as &$achievement)
          $achievement = $db->where("achievement_id", $achievement)->getOne("achievements", "name, image");

    $reward['components'] = unserialize($reward['components']);
    $reward['applications'] = unserialize($reward['applications']);

    foreach($reward['components'] as &$component)
      $component = array_merge($component, $db->where('component_id', $component['component_id'])->getOne('components'));

    foreach($reward['applications'] as &$app)
      $app = array_merge($app, $db->where('app_id', $app['app_id'])->getOne('applications'));

    $tVars["reward"] = $reward;
    $tVars["display"] = "rewards/reward.tpl";
  } else $cardinal->redirect(URL."rewards");

}
else
{
	$rewardsToReceive = $db->where('user_id', $user['id'])->where("received is null")->getOne("user_rewards", "count(*) rewardsToReceive");
	if ($rewardsToReceive['rewardsToReceive'] != $user['rewardsToReceive'])
	{
		$uclass->updatePlayer(array('rewardsToReceive' => $rewardsToReceive['rewardsToReceive']));
		$cardinal->redirect(URL_C);
	}
  if ($user['rewardsToReceive'] && $_POST['claim'])
  {
	  $rewards = $db->where('user_id', $user['id'])->where("received is null")->get("user_rewards");
	  foreach ($rewards as $reward)
		  $uclass->claimReward($reward['reward_id'], $reward);
	  $success[] = "Rewards claimed";
	  $cardinal->redirect($url);
  }
  $rewards = $db->where('user_id', $user['id'])->getOne('user_rewards', 'count(*) nrr');

  $pages                 = new Paginator;
  $pages->items_total    = $rewards['nrr'];
  $pages->paginate();

  $rewards = $db->where("user_id", $user["id"])->orderBy("created", "desc")
	              ->get("user_rewards", $pages->limit, "title,reward_id,received, created");
  $tVars["rewards"] = $rewards;
  $tVars["display"] = "rewards/rewards.tpl";
}
