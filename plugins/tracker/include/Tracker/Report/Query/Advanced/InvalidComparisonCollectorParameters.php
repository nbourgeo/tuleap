<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

namespace Tuleap\Tracker\Report\Query\Advanced;

use PFUser;
use Tracker;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\VisitorParameters;

class InvalidComparisonCollectorParameters implements VisitorParameters
{
    /**
     * @var PFUser
     */
    private $user;

    /**
     * @var Tracker
     */
    private $tracker;

    /**
     * @var InvalidSearchablesCollection
     */
    private $invalid_searchables_collection;

    public function __construct(
        PFUser $user,
        Tracker $tracker,
        InvalidSearchablesCollection $invalid_searchables_collection
    ) {
        $this->user                           = $user;
        $this->tracker                        = $tracker;
        $this->invalid_searchables_collection = $invalid_searchables_collection;
    }

    /**
     * @return PFUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Tracker
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    /**
     * @return InvalidSearchablesCollection
     */
    public function getInvalidSearchablesCollection()
    {
        return $this->invalid_searchables_collection;
    }
}
