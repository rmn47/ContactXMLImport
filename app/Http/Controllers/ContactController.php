<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use SimpleXMLElement;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->paginate(10);
        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        Contact::create($validated);
        return redirect()->route('contacts.index')->with('success', 'Contact created successfully');
    }

    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $contact->update($validated);
        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully');
    }

    public function importForm()
    {
        return view('contacts.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimes:xml'
        ]);

        try {
            $xmlContent = file_get_contents($request->file('xml_file')->path());
            $xml = new SimpleXMLElement($xmlContent);
            
            $imported = 0;
            foreach ($xml->contact as $contactData) {
                // Convert XML element to array
                $data = [];
                foreach ($contactData as $key => $value) {
                    $data[strtolower((string)$key)] = trim((string)$value);
                }

                // Handle different possible formats
                $contactInfo = $this->parseContactData($data);
                
                if ($contactInfo) {
                    Contact::create($contactInfo);
                    $imported++;
                }
            }

            return redirect()
                ->route('contacts.index')
                ->with('success', "Successfully imported {$imported} contacts");
        } catch (\Exception $e) {
            return redirect()
                ->route('contacts.import.form')
                ->with('error', 'Error importing contacts: ' . $e->getMessage());
        }
    }

    private function parseContactData($data)
    {
        // Initialize contact info
        $contactInfo = [
            'first_name' => '',
            'last_name' => '',
            'phone' => ''
        ];

        // Case 1: first_name, last_name, phone format
        if (isset($data['first_name']) && isset($data['last_name']) && isset($data['phone'])) {
            $contactInfo['first_name'] = $data['first_name'];
            $contactInfo['last_name'] = $data['last_name'];
            $contactInfo['phone'] = $data['phone'];
        }
        // Case 2: firstname, lastname, phone format
        elseif (isset($data['firstname']) && isset($data['lastname']) && isset($data['phone'])) {
            $contactInfo['first_name'] = $data['firstname'];
            $contactInfo['last_name'] = $data['lastname'];
            $contactInfo['phone'] = $data['phone'];
        }
        // Case 3: name and contact/phone format
        elseif (isset($data['name']) && (isset($data['contact']) || isset($data['phone']))) {
            // Split name into first and last name
            $nameParts = explode(' ', $data['name'], 2);
            $contactInfo['first_name'] = $nameParts[0];
            $contactInfo['last_name'] = $nameParts[1] ?? ''; // Use empty string if no last name
            $contactInfo['phone'] = $data['contact'] ?? $data['phone'];
        }
        // Case 4: name and number format
        elseif (isset($data['name']) && isset($data['number'])) {
            $nameParts = explode(' ', $data['name'], 2);
            $contactInfo['first_name'] = $nameParts[0];
            $contactInfo['last_name'] = $nameParts[1] ?? '';
            $contactInfo['phone'] = $data['number'];
        }
        // Case 5: single name field and phone
        elseif (isset($data['name'])) {
            $nameParts = explode(' ', $data['name'], 2);
            $contactInfo['first_name'] = $nameParts[0];
            $contactInfo['last_name'] = $nameParts[1] ?? '';
            
            // Look for phone in various possible fields
            foreach (['phone', 'contact', 'number', 'tel', 'telephone'] as $phoneField) {
                if (isset($data[$phoneField])) {
                    $contactInfo['phone'] = $data[$phoneField];
                    break;
                }
            }
        }

        // Validate that we have at least a first name and phone number
        if (!empty($contactInfo['first_name']) && !empty($contactInfo['phone'])) {
            // Clean phone number
            $contactInfo['phone'] = preg_replace('/[^0-9+\s]/', '', $contactInfo['phone']);
            return $contactInfo;
        }

        return null;
    }
}
