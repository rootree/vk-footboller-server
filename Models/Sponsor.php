<?php

/**
 * Description of Sponsor
 *
 * @author Administrator
 */
class Sponsor {

    private $requiredLevel;

    private $energy;

    private $id;
/*
    public function initById($userId) {

        $sponsorInstance = RAM::getInstance()->getSponsorById($userId);

        if($sponsorInstance === false){
            $sql_template =
"SELECT * FROM sponsors
LEFT JOIN item_sponsors ON item_sponsors.id = sponsors.sponsor_id
WHERE sponsor_id = '%s'";

            $sql = sprintf($sql_template,
                intval($userId)
            );
            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            if($SQLresult->num_rows){

                $this->initFromParameters($SQLresult->fetch_object());

                $sponsorInRAM = RAM::getInstance()->getSponsorById($this->getId(), UserParameters::getUserId());
                if($sponsorInRAM === false){
                    $currentIndex = RAM::getInstance()->getMaxObjectIndexForTeam(UserParameters::getUserId(), RAM::RAM_TYPE_SPONSOR);;
                    RAM::getInstance()->setSponsor($this, UserParameters::getUserId(), $currentIndex);
                }else{
                    RAM::getInstance()->setSponsor($this, UserParameters::getUserId());
                }

                $currentIndex = RAM::getInstance()->getMaxObjectIndexForTeam(UserParameters::getUserId(), RAM::RAM_TYPE_SPONSOR);;
                RAM::getInstance()->setSponsor($this, UserParameters::getUserId(), $currentIndex);

            }else{
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Не найден спонсор по номеру ", ErrorPoint::TYPE_SYSTEM);
            }
        }else{
            $this->initFromParameters($sponsorInstance);
        }

    }
*/
    public function initFromParameters($sponsorParameters) {
        $this->energy = $sponsorParameters->energy;
        $this->id = $sponsorParameters->id;
        $this->requiredLevel = (isset($sponsorParameters->required_level)) ? $sponsorParameters->required_level : $sponsorParameters->requiredLevel;
    }

    public function save() {

        $sql_template =
"INSERT INTO sponsors (
    sponsor_id,
    vk_id
) VALUES (
    %d,
    %d
)";

        $sql = sprintf($sql_template,
            $this->getId(),
            UserParameters::getUserId()
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $sponsor = RAM::getInstance()->getSponsorById($this->getId(), UserParameters::getUserId()); 
        if(empty($sponsor)){
            $currentIndex = RAM::getInstance()->getMaxObjectIndexForTeam(UserParameters::getUserId(), RAM::RAM_TYPE_SPONSOR);;
            RAM::getInstance()->setSponsor($this, UserParameters::getUserId(), $currentIndex);
        }else{
            RAM::getInstance()->setSponsor($this, UserParameters::getUserId());
        }

    }

    public function getEnergy(){
        return $this->energy;
    }

    public function getId(){
        return $this->id;
    }

    public function getRequiredLevel(){
        return $this->requiredLevel;
    }
}
?>
