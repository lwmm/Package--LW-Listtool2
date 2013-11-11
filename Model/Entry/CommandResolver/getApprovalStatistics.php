<?php
/**
 *              /F100/ + /F110/ + /F120/
 * 
 * Genehmigungsstatistik laden und im Statusdialog anzeigen.
 */

namespace LwListtool\Model\Entry\CommandResolver;

class getApprovalStatistics extends \LWmvc\Model\CommandResolver
{

    public function __construct($command)
    {
        parent::__construct($command);
        $this->dic = new \LwListtool\Services\dic();
        $this->baseNamespace = "\\LwListtool\\Model\\Entry\\";
        $this->ObjectClass = $this->baseNamespace . "Object\\entry";
    }

    public function getInstance($command)
    {
        return new getApprovalStatistics($command);
    }

    public function resolve()
    {
        $results = array();
        $votedUsers = array();
        $emailsOfNotVotedUsers = array();

        # Dem Listtool zugewiesene Intranets laden
        $intranets = $this->getQueryHandler()->getIntranetsByListid($this->command->getParameterByKey('listId'));
        
        # Dem Listtool direkt zugewiesene Nutzer laden
        $users = $this->getQueryHandler()->getUserByListid($this->command->getParameterByKey('listId'));
        
        # Stimmabgaben für dieses Listtool und der entsprechenden Eintrags-ID laden
        $votes = $this->getQueryHandler()->getVotingsByEntryId($this->command->getParameterByKey('id'), $this->command->getParameterByKey('listId'));


        # Die einzelnen Nutzer der zugewiesenen Intranets laden.
        foreach ($intranets as $intranet) {
            $intraUser = $this->getQueryHandler()->getUserByIntranetId($intranet["id"]);
            foreach ($intraUser as $inUser) {
                array_push($users, $inUser);
            }
        }

        $yes = $no = 0;
        foreach ($votes as $vote) {
            $votedUsers[$vote["lw_first_user"]] = true;

            if ($vote["opt1bool"] == 1) {
                $yes++;
            }
            else {
                $no++;
                $results["comments"][] = $vote["opt1text"];
            }
        }

        foreach ($users as $user) {
            if (!array_key_exists($user["id"], $votedUsers) && !empty($user["email"])) {
                $emailsOfNotVotedUsers[$user["email"]] = true;
            }
        }
        
        $results["participants"] = count($users);
        $results["participant_quote"] = round(count($votes) / count($users) * 100, 2);
        $results["voted"] = count($votes);
        
        # /F050/ die reinen "Ja"-Stimmen sind relevent für die Genehmigung. "Nein"-Stimmen
        # und Enthaltungen reduzieren somit den Anteil von "Ja"-Stimmen.
        $results["voted_yes_percent"] = round($yes / count($users) * 100, 2);
        $results["voted_no_percent"] = round($no / count($users) * 100, 2);
        $results["voted_not_percent"] = round( ( count($users) - ( $yes + $no ) ) / count($users) * 100, 2);
        
        $results["voted_yes"] = $yes;
        $results["voted_no"] = $no;
        $results["voted_not"] = count($users) - ( $yes + $no );

        $this->command->getResponse()->setDataByKey('results', $results);
        $this->command->getResponse()->setDataByKey('emailsOfNotVotedUsers', $emailsOfNotVotedUsers);
        return $this->command->getResponse();
    }

}