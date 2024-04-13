<?php

class AprioriAlgorithm {

    private $minimumSupportCount;
    private $minimumSupport;
    private $minimumConfidence;

    // Here $transactions is an array of arrays even if it consists of one element, then this one integer
    // element will be in an array and this array is the first and only element in the outer main array $transactions.
    private $transactions;

    public function __construct() {
        $this->minimumSupportCount = 5;
        $this->minimumSupport = 0.01;
        $this->minimumConfidence = 0.01;
    }
    

    public function setMinimumSupportCount($minimumSupportCount) {
        $this->minimumSupportCount = $minimumSupportCount;
    }

    public function setMinimumSupport($minimumSupport) {
        $this->minimumSupport = $minimumSupport;
    }

    public function setMinimumConfidence($minimumConfidence) {
        $this->minimumConfidence = $minimumConfidence;
    }

    public function setTransactions($transactions) {
        $this->transactions = $transactions;
    }

    public function getMinimumSupportCount() {
        return $this->minimumSupportCount;
    }
    
    public function getMinimumSupport() {
        return $this->minimumSupport;
    }
    
    public function getMinimumConfidence() {
        return $this->minimumConfidence;
    }
    
    public function getTransactions() {
        return $this->transactions;
    }

    // The following method validates the current object
    public function validateCurrentObject() {
        if (($this->transactions == null) || (empty($this->transactions))) {
            return false;
        }
        foreach ($this->transactions as $array) {
            if (!is_array($array)) {
                return false;
            }
            foreach ($array as $element) {
                if (!is_int($element)) {
                    return false;
                }
            }
        }

        if ((!isset($this->minimumSupportCount)) || (!is_numeric($this->minimumSupportCount)) ||
            ($this->minimumSupportCount < 0) || (floor($this->minimumSupportCount) != $this->minimumSupportCount)) {
            return false;
        }
        
        if ((!isset($this->minimumSupport)) || (!is_numeric($this->minimumSupport)) || ($this->minimumSupportCount < 0)) {
            return false;
        }

        if ((!isset($this->minimumConfidence)) || (!is_numeric($this->minimumConfidence)) || ($this->minimumConfidence < 0)) {
            return false;
        }

        return true;
    }

    // The following method expects an array or arrays always and returns the same type
    public function generateFrequentItems($currentItems) {

        // Sorting each item independently as a first step
        foreach ($currentItems as $currentItem) {
           sort($currentItem);
        }

        $frequencyItem = array();
        foreach ($this->transactions as $transaction) {

            $visitedTag = array();
            foreach ($transaction as $tagID) {
                $visitedTag[$tagID] = true;
            }

            foreach ($currentItems as $currentItem) {
            
                $ok = true;
                
                foreach ($currentItem as $tagID) {
                    if (!isset($visitedTag[$tagID])) {
                        $ok = false;
                        break;
                    }
                }

                if ($ok) {
                    $frequencyItem[serialize($currentItem)] = isset($frequencyItem[serialize($currentItem)]) ?
                                                              $frequencyItem[serialize($currentItem)] + 1 : 1;
                }

            }

        }

        $frequentItems = array();
        foreach ($currentItems as $currentItem) {
            if ((isset($frequencyItem[serialize($currentItem)])) && 
                ($frequencyItem[serialize($currentItem)] >= $this->minimumSupportCount)) {

                $frequentItems[] = $currentItem;

            }
        }

        return $frequentItems;

    }

    
    public function generateItemsForNextLevel($currentItems) {

        $nextLevelItems = array();
        // Iterate over each pair of lists in the currentItems array
        for ($i = 0; $i < count($currentItems); $i++) {
            $list1 = $currentItems[$i];
            for ($j = $i + 1; $j < count($currentItems); $j++) {
                $list2 = $currentItems[$j];
    
                // Check if the two lists have the same prefix except for the last element
                $samePrefix = true;
                for ($k = 0; $k < count($list1) - 1; $k++) {
                    if ($list1[$k] != $list2[$k]) {
                        $samePrefix = false;
                        break;
                    }
                }
    
                if ($samePrefix && ($list1[count($list1) - 1] != $list2[count($list2) - 1])) {
                    // Generate the new item by appending the last element of list2 to list1
                    $newItem = array_merge($list1, array($list2[count($list2) - 1]));
                    sort($newItem);
                    $nextLevelItems[] = $newItem;
                }
            }
        }
    
        return $nextLevelItems;

    }


    public function generateAssociationRules($currentItems) {

        $linkingList = array();
        // Iterate over each item in the currentItems array
        foreach ($currentItems as $currentItem) {

            $numTags = count($currentItem);
            
            for ($mask = 1; $mask < (1 << $numTags) - 1; $mask++) {

                $visitedTagIndex = array();
                $subsetItems = array();
                for ($bit = 0; $bit < $numTags; $bit++) {
                    if ($mask & (1 << $bit)) {
                        $subsetItems[] = $currentItem[$bit];
                        $visitedTagIndex[$bit] = true;
                    }
                }

                $otherItems = array();
                for ($i = 0; $i < $numTags; $i++) {
                    if (!isset($visitedTagIndex[$i])) {
                        $otherItems[] = $currentItem[$i];
                    }
                }

                // Link the current item with the other items in the following $linkingList array
                // And in my case, I don't need to check the $minimumSupport and the $minimumConfidence so far
                $linkingList[serialize($subsetItems)][] = $otherItems;

            }
            
        }
        
        return $linkingList;

    }


    // The following recursive method returns the final association rules in a form of
    // an associative array where each key is a serialized string of a sorted array and each value is an array of
    // arrays (even if it contains one array and that array contains one integer element) where each inner array is sorted.
    // Also the parameter $currentItems is an array of arrays even if it contains one element.
    public function apriori($currentItems, $level, $maxLevel) {

        if ((!$this->validateCurrentObject()) || ($currentItems == null) || empty($currentItems)) {
            return null;
        }
        // Check if the current state is the base case of this recursive function
        if ($level == $maxLevel) {

            // Step 1: Generate frequent itemsets
            $frequentItems = $this->generateFrequentItems($currentItems);
            if (($frequentItems == null) || empty($frequentItems)) {
                return null;
            }
            
            // Final Step: Generate the corresponding association rules and return them as the final result
            $associationRules = $this->generateAssociationRules($frequentItems);
            if (($associationRules == null) || empty($associationRules)) {
                return null;
            }

            return $associationRules;

        }

        // Step 1: Generate frequent itemsets
        $frequentItems = $this->generateFrequentItems($currentItems);
        if (($frequentItems == null) || empty($frequentItems)) {
            return null;
        }

        // Step 2: Generate items for the next level, and call the recursion
        $itemsForNextLevel = $this->generateItemsForNextLevel($frequentItems);

        return $this->apriori($itemsForNextLevel, $level + 1, $maxLevel);
        
    }

}


?>